const laravelService = require('../../services/laravelService');
const { errorEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [🔑 Get Key] — Public Panel.
 * Menampilkan license key milik user yang sedang berinteraksi.
 * Respon ephemeral dan otomatis terhapus setelah 20 detik.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleGetKey(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const discordId = interaction.user.id;
  const result = await laravelService.getLicenseStats(discordId);

  if (!result.success) {
    await interaction.editReply({
      embeds: [
        errorEmbed(
          result.message ||
            'Kamu belum memiliki lisensi. Minta Admin untuk generate key terlebih dahulu.'
        ),
      ],
    });
    autoDeleteReply(interaction);
    return;
  }

  const license = result.data?.data;

  if (!license?.key) {
    await interaction.editReply({
      embeds: [errorEmbed('Lisensi ditemukan, tetapi key tidak tersedia. Hubungi Admin.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  await interaction.editReply({
    embeds: [
      {
        color: 0x57f287,
        title: '🔑 License Key Anda',
        description: [
          'Berikut adalah license key milik Anda:',
          `\`\`\`${license.key}\`\`\``,
          '_Salin key di atas, lalu paste ke script loader kamu._',
        ].join('\n'),
        fields: [
          { name: 'Status',        value: formatStatus(license.status),              inline: true },
          { name: 'Berlaku Hingga',value: license.expires_at_human || 'Lifetime',    inline: true },
          { name: 'Tipe',          value: license.license_type === 'admin' ? '⭐ Admin' : '👤 User', inline: true },
        ],
        footer: { text: '⚠️ Jangan bagikan key ini! • Pesan otomatis hilang dalam 20 detik.' },
        timestamp: new Date().toISOString(),
      },
    ],
  });

  autoDeleteReply(interaction);
}

function formatStatus(status) {
  if (!status) return '❔ Tidak diketahui';
  const s = String(status).toLowerCase();
  if (s === 'active')                        return '🟢 Aktif';
  if (s === 'expired')                       return '🔴 Kadaluarsa';
  if (s === 'banned' || s === 'suspended')   return '⛔ Diblokir';
  return `❔ ${status}`;
}

module.exports = handleGetKey;
