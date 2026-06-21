const { SlashCommandBuilder, EmbedBuilder } = require('discord.js');
const laravelService = require('../../services/laravelService');
const { errorEmbed } = require('../../utils/replyHelper');

module.exports = {
  data: new SlashCommandBuilder()
    .setName('wolf')
    .setDescription('Ngobrol santai bareng WOLF AI')
    .addStringOption((option) =>
      option
        .setName('prompt')
        .setDescription('Mau ngomongin apa sob?')
        .setRequired(true)
        .setMaxLength(1000)
    ),

  /**
   * @param {import('discord.js').ChatInputCommandInteraction} interaction
   */
  async execute(interaction) {
    const prompt = interaction.options.getString('prompt');
    const user = interaction.user;

    // Tampilkan status "thinking..." secara publik agar seperti sedang mengetik/berpikir
    await interaction.deferReply({ ephemeral: false });

    // Panggil Backend Laravel untuk chat dengan AI
    const result = await laravelService.askAi(prompt, user.id);

    if (!result.success) {
      await interaction.editReply({
        embeds: [errorEmbed(result.message || 'Gagal tersambung ke backend AI.')],
      });
      return;
    }

    const aiResponse = result.data?.data || 'Gak ada jawaban sob, lagi ngelamun kali gw.';

    // Kirim jawaban dengan layout premium
    const chatEmbed = new EmbedBuilder()
      .setColor(0x5865f2)
      .setAuthor({
        name: `Teman Ngobrol: ${user.displayName || user.username}`,
        iconURL: user.displayAvatarURL({ dynamic: true }),
      })
      .setDescription([
        `💬 **Kamu:** ${prompt}`,
        '',
        `🐺 **Wolf:** ${aiResponse}`
      ].join('\n'))
      .setFooter({ text: 'Wolf AI' })
      .setTimestamp();

    await interaction.editReply({
      embeds: [chatEmbed],
    });
  },
};
