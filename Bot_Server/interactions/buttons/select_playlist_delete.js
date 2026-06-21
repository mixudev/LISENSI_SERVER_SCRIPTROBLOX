const { removeTrack } = require('../../services/playlistService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler ketika user memilih lagu untuk dihapus dari dropdown menu.
 *
 * @param {import('discord.js').StringSelectMenuInteraction} interaction
 */
async function handleSelectPlaylistDelete(interaction) {
  const index   = parseInt(interaction.values[0], 10);
  const userId  = interaction.user.id;
  const guildId = interaction.guildId;

  const result = removeTrack(userId, guildId, index);

  if (result.success) {
    await interaction.update({
      embeds: [successEmbed('Lagu Dihapus', result.message)],
      components: [],
    });
    autoDeleteReply(interaction);
  } else {
    await interaction.update({
      embeds: [errorEmbed(result.message)],
      components: [],
    });
    autoDeleteReply(interaction);
  }
}

module.exports = handleSelectPlaylistDelete;
