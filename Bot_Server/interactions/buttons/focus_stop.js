const focusManager = require('../../services/focusManager');
const config = require('../../config');
const { forbiddenEmbed } = require('../../utils/replyHelper');

/**
 * Handler ketika tombol Hentikan Pomodoro ditekan.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleFocusStop(interaction) {
  const guildId = interaction.guildId;
  const session = focusManager.getSession(guildId);

  if (!session) {
    await interaction.reply({
      content: '❌ Tidak ada sesi Focus Timer yang aktif di server ini.',
      ephemeral: true,
    });
    return;
  }

  // Pengecekan izin
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

  // Hentikan sesi
  focusManager.stopSession(guildId);

  // Edit status panel memberitahu bahwa sesi dihentikan
  try {
    await interaction.editReply({
      content: '⏹️ Sesi Focus Timer telah dihentikan.',
      embeds: [],
      components: [],
    });
  } catch (err) {
    // Abaikan jika interaksi ditutup
  }
}

module.exports = handleFocusStop;
