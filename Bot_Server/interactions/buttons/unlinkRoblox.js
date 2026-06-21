const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [❌ Hapus Kaitan] — Roblox Panel.
 * Menghapus kaitan akun Roblox dari user ini.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleUnlinkRoblox(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const discordId = interaction.user.id;
  const result = await laravelService.linkRoblox(discordId, null);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal menghapus kaitan akun Roblox Anda.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  await interaction.editReply({
    embeds: [
      successEmbed(
        '❌ Kaitan Akun Dihapus',
        [
          'Kaitan akun Roblox Anda berhasil dihapus.',
          '_Mulai sekarang, lisensi Anda tidak lagi dibatasi oleh username Roblox._',
          '_Pesan ini otomatis hilang dalam 20 detik._',
        ].join('\n')
      ),
    ],
  });

  autoDeleteReply(interaction);
}

module.exports = handleUnlinkRoblox;
