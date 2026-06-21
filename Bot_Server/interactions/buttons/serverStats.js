const config = require('../../config');
const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, forbiddenEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [📊 Server Stats] — Admin Panel.
 * Menampilkan statistik server lisensi secara real-time.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleServerStats(interaction) {
  const hasAdminRole = interaction.member.roles.cache.has(config.discord.adminRoleId);
  const isAdminUser  = config.discord.adminUserIds.includes(interaction.user.id);

  if (!hasAdminRole && !isAdminUser) {
    await interaction.reply({
      embeds: [forbiddenEmbed('Hanya Staff Admin yang dapat melihat statistik server.')],
      ephemeral: true,
    });
    return;
  }

  await interaction.deferReply({ ephemeral: true });

  const result = await laravelService.getServerStats();

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal mengambil statistik server.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const stats = result.data?.data;

  await interaction.editReply({
    embeds: [
      {
        color: 0x57f287,
        title: '📊 Statistik Lisensi Server',
        description: 'Detail status lisensi terdaftar di database saat ini.',
        fields: [
          { name: '🟢 Aktif',        value: `**${stats?.active ?? 0}**`,       inline: true },
          { name: '🔴 Kadaluarsa',   value: `**${stats?.expired ?? 0}**`,      inline: true },
          { name: '⛔ Ditangguhkan', value: `**${stats?.suspended ?? 0}**`,    inline: true },
          { name: '🚫 Dibanned',     value: `**${stats?.banned ?? 0}**`,       inline: true },
          { name: '📁 Total Lisensi',value: `**${stats?.total ?? 0}**`,        inline: true },
        ],
        footer: { text: 'License System • Admin Dashboard' },
        timestamp: new Date().toISOString(),
      },
    ],
  });

  autoDeleteReply(interaction, 20000);
}

module.exports = handleServerStats;
