const { EmbedBuilder } = require('discord.js');
const laravelService = require('../../services/laravelService');
const { errorEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [📜 Get Script] — Public Panel.
 * Mengambil template Loader.lua resmi dari Laravel (sudah include script_key user).
 * Respon ephemeral dan otomatis terhapus setelah 30 detik (lebih lama untuk baca script).
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleGetScript(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const discordId = interaction.user.id;
  const result = await laravelService.getScriptTemplate(discordId);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message)],
    });
    autoDeleteReply(interaction);
    return;
  }

  const payload   = result.data?.data;
  const script    = payload?.script || '';
  const loaderUrl = payload?.loader_url || 'N/A';

  const embed = new EmbedBuilder()
    .setColor(0x5865f2)
    .setTitle('📜 Script Loader LimeHub')
    .setDescription(
      [
        `**Loader URL:** \`${loaderUrl}\``,
        '_Pesan ini otomatis hilang dalam 30 detik._',
      ].join('\n')
    )
    .setFooter({ text: 'LimeHub License System' })
    .setTimestamp();

  await interaction.editReply({
    embeds: [embed],
    content: `\`\`\`lua\n${script}\n\`\`\``,
  });

  // Script perlu lebih lama dibaca — berikan 30 detik
  autoDeleteReply(interaction, 30_000);
}

module.exports = handleGetScript;
