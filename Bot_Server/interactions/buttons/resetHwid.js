const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [🔄 Reset HWID] — Public Panel.
 * Reset HWID lisensi milik Discord user yang mengklik.
 * Respon ephemeral dan otomatis terhapus setelah 20 detik.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleResetHwid(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const discordId = interaction.user.id;
  const result = await laravelService.resetHwid(discordId);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message)],
    });
    autoDeleteReply(interaction);
    return;
  }

  const resetCount = result.data?.data?.hwid_reset_count;

  await interaction.editReply({
    embeds: [
      successEmbed(
        '🔄 HWID Berhasil Direset',
        [
          result.message || 'HWID pada lisensi Anda telah dikosongkan.',
          resetCount !== undefined ? `Total reset: **${resetCount}x**` : null,
          '',
          'Jalankan ulang loader di perangkat/game baru.',
          '',
          '_Pesan ini otomatis hilang dalam 20 detik._',
        ]
          .filter(Boolean)
          .join('\n')
      ),
    ],
  });

  autoDeleteReply(interaction);
}

module.exports = handleResetHwid;
