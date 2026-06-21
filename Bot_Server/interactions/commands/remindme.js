const { SlashCommandBuilder, EmbedBuilder } = require('discord.js');
const { add } = require('../../services/reminderService');
const { parseTime, formatDate } = require('../../utils/timeParser');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

module.exports = {
  data: new SlashCommandBuilder()
    .setName('remindme')
    .setDescription('Mengatur pengingat cerdas.')
    .addStringOption((option) =>
      option
        .setName('durasi')
        .setDescription('Waktu/durasi pengingat (contoh: 30m, 2h, 1d)')
        .setRequired(true)
    )
    .addStringOption((option) =>
      option
        .setName('pesan')
        .setDescription('Pesan pengingat')
        .setRequired(true)
    )
    .addMentionableOption((option) =>
      option
        .setName('tag')
        .setDescription('Role atau User yang ingin di-tag saat pengingat berbunyi (opsional)')
        .setRequired(false)
    ),

  /**
   * @param {import('discord.js').ChatInputCommandInteraction} interaction
   */
  async execute(interaction) {
    await interaction.deferReply({ ephemeral: true });

    const rawTime = interaction.options.getString('durasi').trim();
    const message = interaction.options.getString('pesan').trim();
    const tagObj  = interaction.options.getMentionable('tag');

    // 1. Parse Waktu
    const ms = parseTime(rawTime);
    if (!ms || ms <= 0) {
      await interaction.editReply({
        embeds: [errorEmbed(`Format waktu **"${rawTime}"** tidak valid.\nGunakan format seperti: \`30m\`, \`2h\`, \`1d\`, \`1 hour\`, \`3 days\`.`)]
      });
      autoDeleteReply(interaction);
      return;
    }

    const fireAt = new Date(Date.now() + ms);

    // 2. Format targetTag
    let targetTag = null;
    if (tagObj) {
      targetTag = tagObj.toString(); // format: <@userId> atau <@&roleId>
    }

    try {
      // 3. Simpan & Jadwalkan
      add({
        userId: interaction.user.id,
        guildId: interaction.guildId,
        message,
        fireAt,
        targetTag,
      });

      const timeStr = formatDate(fireAt);
      const tagMsg = targetTag ? `\n**Target Tag:** ${targetTag}` : '';

      await interaction.editReply({
        embeds: [
          successEmbed(
            'Pengingat Berhasil Diatur!',
            [
              `Saya akan mengingatkan Anda pada:`,
              `**\`${timeStr}\`** (sekitar ${rawTime} lagi)`,
              ``,
              `> **Pesan:** ${message}`,
              tagMsg,
            ].join('\n')
          )
        ]
      });
      autoDeleteReply(interaction);
    } catch (err) {
      console.error('❌ [/remindme] Gagal menambahkan pengingat:', err);
      await interaction.editReply({
        embeds: [errorEmbed('Gagal menyimpan pengingat. Silakan hubungi Admin.')]
      });
      autoDeleteReply(interaction);
    }
  },
};
