const laravelService = require('../../services/laravelService');
const { errorEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [📊 Get Stats] — Public Panel.
 * Respon ephemeral dan otomatis terhapus setelah 20 detik.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleGetStats(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const discordId = interaction.user.id;
  const result = await laravelService.getLicenseStats(discordId);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message)],
    });
    autoDeleteReply(interaction);
    return;
  }

  const license = result.data?.data;

  const statsEmbed = {
    color: 0x5865f2,
    title: '📊 Statistik Lisensi Anda',
    fields: [
      { name: 'License Key',     value: `\`${license.key || 'N/A'}\``,                               inline: false },
      { name: 'Status',          value: formatStatus(license.status),                                  inline: true  },
      { name: 'Tipe',            value: license.license_type === 'admin' ? '⭐ Admin' : '👤 User',     inline: true  },
      { name: 'Terkunci di HWID',value: license.hwid ? `\`${license.hwid}\`` : '_Belum terkunci_',    inline: false },
      { name: 'Reset HWID',      value: `${license.hwid_reset_count ?? 0}x`,                          inline: true  },
      { name: 'Berlaku Hingga',  value: license.expires_at_human || 'Lifetime',                       inline: true  },
    ],
    footer: { text: 'LimeHub License System • Pesan otomatis hilang dalam 20 detik.' },
    timestamp: new Date().toISOString(),
  };

  await interaction.editReply({ embeds: [statsEmbed] });
  autoDeleteReply(interaction);
}

function formatStatus(status) {
  if (!status) return '❔ Tidak diketahui';
  const normalized = String(status).toLowerCase();
  if (normalized === 'active')                               return '🟢 Aktif';
  if (normalized === 'expired')                              return '🔴 Kadaluarsa';
  if (normalized === 'banned' || normalized === 'suspended') return '⛔ Diblokir';
  return `❔ ${status}`;
}

module.exports = handleGetStats;
