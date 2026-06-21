const { ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder } = require('discord.js');

const REM_ADD_MODAL_ID      = 'rem_modal_add';
const REM_INPUT_TIME_ID     = 'rem_input_time';
const REM_INPUT_MESSAGE_ID  = 'rem_input_message';
const REM_INPUT_TAG_ID      = 'rem_input_tag';

/**
 * Handler ketika tombol "Tambah Pengingat" ditekan.
 * Membuka modal pengisian reminder.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleReminderAdd(interaction) {
  const modal = new ModalBuilder()
    .setCustomId(REM_ADD_MODAL_ID)
    .setTitle('⏰ Tambah Pengingat Baru');

  const timeInput = new TextInputBuilder()
    .setCustomId(REM_INPUT_TIME_ID)
    .setLabel('Waktu / Durasi')
    .setPlaceholder('Contoh: 30m, 2h, 1d, 5m, 1 hour')
    .setStyle(TextInputStyle.Short)
    .setMinLength(2)
    .setMaxLength(30)
    .setRequired(true);

  const messageInput = new TextInputBuilder()
    .setCustomId(REM_INPUT_MESSAGE_ID)
    .setLabel('Pesan Pengingat')
    .setPlaceholder('Contoh: Mabar Roblox, Istirahat, Daily standup')
    .setStyle(TextInputStyle.Paragraph)
    .setMinLength(3)
    .setMaxLength(500)
    .setRequired(true);

  const tagInput = new TextInputBuilder()
    .setCustomId(REM_INPUT_TAG_ID)
    .setLabel('Tag Role / User (Opsional)')
    .setPlaceholder('Masukkan ID Role/User jika ingin di-tag (e.g. @everyone atau 123456789)')
    .setStyle(TextInputStyle.Short)
    .setRequired(false);

  modal.addComponents(
    new ActionRowBuilder().addComponents(timeInput),
    new ActionRowBuilder().addComponents(messageInput),
    new ActionRowBuilder().addComponents(tagInput)
  );

  await interaction.showModal(modal);
}

module.exports = handleReminderAdd;
module.exports.REM_ADD_MODAL_ID = REM_ADD_MODAL_ID;
module.exports.REM_INPUT_TIME_ID = REM_INPUT_TIME_ID;
module.exports.REM_INPUT_MESSAGE_ID = REM_INPUT_MESSAGE_ID;
module.exports.REM_INPUT_TAG_ID = REM_INPUT_TAG_ID;
