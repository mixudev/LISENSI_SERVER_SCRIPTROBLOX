const { errorEmbed } = require('../../utils/replyHelper');
const focusManager = require('../../services/focusManager');
const { FOCUS_INPUT_DURATION_ID, FOCUS_INPUT_BREAK_ID } = require('../commands/focus');

/**
 * Handler ketika modal konfigurasi Focus Timer disubmit.
 *
 * @param {import('discord.js').ModalSubmitInteraction} interaction
 */
async function handleFocusModalSubmit(interaction) {
  // Wajib ephemeral agar hanya pembuat yang bisa melihat status panel realtime
  await interaction.deferReply({ ephemeral: true });

  const voiceChannel = interaction.member.voice.channel;
  if (!voiceChannel) {
    await interaction.editReply({
      embeds: [errorEmbed('Anda terputus dari Voice Channel. Silakan gabung kembali!')],
    });
    return;
  }

  const rawFocus = interaction.fields.getTextInputValue(FOCUS_INPUT_DURATION_ID).trim();
  const rawBreak = interaction.fields.getTextInputValue(FOCUS_INPUT_BREAK_ID).trim();

  const focusDuration = parseInt(rawFocus, 10);
  const breakDuration = parseInt(rawBreak, 10);

  if (isNaN(focusDuration) || focusDuration <= 0 || isNaN(breakDuration) || breakDuration <= 0) {
    await interaction.editReply({
      embeds: [errorEmbed('Durasi fokus dan istirahat harus berupa angka bulat positif!')],
    });
    return;
  }

  try {
    await focusManager.startSession({
      interaction,
      voiceChannel,
      focusDuration,
      breakDuration,
    });
  } catch (err) {
    console.error('❌ [Focus Modal] Gagal memulai sesi:', err);
    await interaction.editReply({
      embeds: [errorEmbed('Gagal memulai sesi Focus Timer. Silakan coba lagi.')],
    });
  }
}

module.exports = handleFocusModalSubmit;
