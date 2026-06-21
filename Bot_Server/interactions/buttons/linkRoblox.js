const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const laravelService = require('../../services/laravelService');
const { errorEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [🎮 Kaitkan Akun] — Roblox Panel.
 * Mengembalikan link web ephemeral untuk menghubungkan akun Roblox asli.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleLinkRoblox(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const discordId = interaction.user.id;
  const result = await laravelService.getRobloxLinkUrl(discordId);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal memproses permintaan kaitan akun.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const linkUrl = result.data?.data?.url;

  if (!linkUrl) {
    await interaction.editReply({
      embeds: [errorEmbed('Gagal mengambil URL otorisasi dari server backend.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const embed = new EmbedBuilder()
    .setColor(0xff5a00)
    .setTitle('🎮 Hubungkan Akun Roblox Anda')
    .setDescription(
      [
        'Silakan klik tombol di bawah untuk membuka halaman web kaitan.',
        '',
        'Sesi web akan membaca akun Roblox yang sedang aktif/login di perangkat Anda.',
        '',
        '⚠️ **Perhatian**: Jangan bagikan link ini kepada siapapun.',
        '',
        '_Pesan ini akan terhapus otomatis dalam 30 detik._',
      ].join('\n')
    );

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setLabel('Kaitkan Akun (Web)')
      .setEmoji('🔗')
      .setURL(linkUrl)
      .setStyle(ButtonStyle.Link)
  );

  await interaction.editReply({
    embeds: [embed],
    components: [row],
  });

  autoDeleteReply(interaction, 30000);
}

module.exports = handleLinkRoblox;
