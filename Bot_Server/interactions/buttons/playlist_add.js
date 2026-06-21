const { ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder } = require('discord.js');

const PL_ADD_MODAL_ID      = 'pl_modal_add';
const PL_INPUT_TITLE_ID     = 'pl_input_title';
const PL_INPUT_URL_ID       = 'pl_input_url';

/**
 * Handler ketika tombol "Tambah Lagu" ditekan.
 * Membuka modal pengisian judul lagu & link YouTube.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handlePlaylistAdd(interaction) {
  const modal = new ModalBuilder()
    .setCustomId(PL_ADD_MODAL_ID)
    .setTitle('🎵 Tambah Lagu ke Playlist');

  const titleInput = new TextInputBuilder()
    .setCustomId(PL_INPUT_TITLE_ID)
    .setLabel('Judul Lagu / Deskripsi')
    .setPlaceholder('Contoh: Lofi Study Chill, Relaxing Piano')
    .setStyle(TextInputStyle.Short)
    .setMinLength(2)
    .setMaxLength(100)
    .setRequired(true);

  const urlInput = new TextInputBuilder()
    .setCustomId(PL_INPUT_URL_ID)
    .setLabel('Link Video YouTube')
    .setPlaceholder('Contoh: https://www.youtube.com/watch?v=xxxx')
    .setStyle(TextInputStyle.Short)
    .setMinLength(15)
    .setMaxLength(150)
    .setRequired(true);

  modal.addComponents(
    new ActionRowBuilder().addComponents(titleInput),
    new ActionRowBuilder().addComponents(urlInput)
  );

  await interaction.showModal(modal);
}

module.exports = handlePlaylistAdd;
module.exports.PL_ADD_MODAL_ID = PL_ADD_MODAL_ID;
module.exports.PL_INPUT_TITLE_ID = PL_INPUT_TITLE_ID;
module.exports.PL_INPUT_URL_ID = PL_INPUT_URL_ID;
