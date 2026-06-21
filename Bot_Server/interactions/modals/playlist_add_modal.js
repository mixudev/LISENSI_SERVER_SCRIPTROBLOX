const ytdl = require('@distube/ytdl-core');
const { addTrack } = require('../../services/playlistService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');
const { PL_INPUT_TITLE_ID, PL_INPUT_URL_ID } = require('../buttons/playlist_add');

/**
 * Handler ketika modal tambah lagu disubmit.
 *
 * @param {import('discord.js').ModalSubmitInteraction} interaction
 */
async function handlePlaylistModalSubmit(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const title = interaction.fields.getTextInputValue(PL_INPUT_TITLE_ID).trim();
  const url   = interaction.fields.getTextInputValue(PL_INPUT_URL_ID).trim();

  // 1. Validasi Link YouTube
  const isValidUrl = ytdl.validateURL(url);
  if (!isValidUrl) {
    await interaction.editReply({
      embeds: [errorEmbed('Format Link YouTube tidak valid. Harap masukkan URL YouTube yang benar.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  try {
    // 2. Tambah lagu ke playlist
    const result = addTrack(interaction.user.id, interaction.guildId, { title, url });

    if (result.success) {
      await interaction.editReply({
        embeds: [successEmbed('Lagu Ditambahkan', result.message)],
      });
    } else {
      await interaction.editReply({
        embeds: [errorEmbed(result.message)],
      });
    }
    autoDeleteReply(interaction);
  } catch (err) {
    console.error('❌ [Playlist Modal] Gagal menambahkan lagu:', err);
    await interaction.editReply({
      embeds: [errorEmbed('Gagal menyimpan lagu. Silakan hubungi Admin.')],
    });
    autoDeleteReply(interaction);
  }
}

module.exports = handlePlaylistModalSubmit;
