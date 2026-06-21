const focusManager = require('../../services/focusManager');
const config = require('../../config');
const { forbiddenEmbed } = require('../../utils/replyHelper');

/**
 * Handler ketika tombol Pause/Resume Pomodoro ditekan.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleFocusPause(interaction) {
  const guildId = interaction.guildId;
  const session = focusManager.getSession(guildId);

  if (!session) {
    await interaction.reply({
      content: '❌ Tidak ada sesi Focus Timer yang aktif di server ini.',
      ephemeral: true,
    });
    return;
  }

  // Pengecekan izin: Hanya pembuat sesi atau Admin yang bisa pause/resume
  const hasAdminRole = interaction.member.roles.cache.has(config.discord.adminRoleId);
  const isAdminUser  = config.discord.adminUserIds.includes(interaction.user.id);
  const isCreator    = interaction.user.id === session.creatorId;
  const hasPermission = isCreator || hasAdminRole || isAdminUser;

  if (!hasPermission) {
    await interaction.reply({
      embeds: [forbiddenEmbed('Hanya pembuat sesi Focus Timer yang dapat mengendalikannya.')],
      ephemeral: true,
    });
    return;
  }

  await interaction.deferUpdate();
  focusManager.togglePause(guildId);
}

module.exports = handleFocusPause;
