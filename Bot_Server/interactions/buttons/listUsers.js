const config = require('../../config');
const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, forbiddenEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [👥 List Users] — Admin Panel.
 * Menampilkan daftar lisensi aktif terbaru.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleListUsers(interaction) {
  const hasAdminRole = interaction.member.roles.cache.has(config.discord.adminRoleId);
  const isAdminUser  = config.discord.adminUserIds.includes(interaction.user.id);

  if (!hasAdminRole && !isAdminUser) {
    await interaction.reply({
      embeds: [forbiddenEmbed('Hanya Staff Admin yang dapat melihat daftar user.')],
      ephemeral: true,
    });
    return;
  }

  await interaction.deferReply({ ephemeral: true });

  const result = await laravelService.listUsers();

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal mengambil daftar user.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const users = result.data?.data || [];

  if (users.length === 0) {
    await interaction.editReply({
      embeds: [successEmbed('👥 Daftar User Lisensi', 'Belum ada user dengan lisensi aktif.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const lines = users.map((u, i) => {
    const hwidStatus = u.hwid_bound ? '🔒 Bound' : '🔓 Unbound';
    return `${i + 1}. **<@${u.discord_id}>** (${u.username})\n   Key: \`${u.key}\`\n   Type: \`${u.license_type}\` | Expired: \`${u.expires_at}\` | HWID: \`${hwidStatus}\``;
  });

  await interaction.editReply({
    embeds: [
      {
        color: 0x5865f2,
        title: '👥 Daftar User Lisensi Aktif (Top 25)',
        description: lines.join('\n\n'),
        footer: { text: 'License System • Admin View' },
        timestamp: new Date().toISOString(),
      },
    ],
  });

  autoDeleteReply(interaction, 60000); // 60 detik agar admin sempat membaca
}

module.exports = handleListUsers;
