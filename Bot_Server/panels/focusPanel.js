/**
 * focusPanel.js — Panel virtual Focus Timer.
 *
 * Karena panel status focus bersifat ephemeral / real-time per user,
 * panel ini tidak memiliki text channel fisik permanen.
 * Kita mendaftarkannya agar PanelManager bisa melakukan routing otomatis
 * untuk tombol-tombol control timer dan modal submit.
 */

const handleFocusPause = require('../interactions/buttons/focus_pause');
const handleFocusSkip  = require('../interactions/buttons/focus_skip');
const handleFocusStop  = require('../interactions/buttons/focus_stop');
const handleFocusModalSubmit = require('../interactions/modals/focus_modal');
const { FOCUS_MODAL_ID } = require('../interactions/commands/focus');

module.exports = {
  name: 'focus',

  getChannelId: () => null, // Virtual panel, tidak dikirim ke channel manapun secara otomatis

  getPayload: () => null,

  buttonHandlers: {
    'focus_btn_pause': handleFocusPause,
    'focus_btn_skip':  handleFocusSkip,
    'focus_btn_stop':  handleFocusStop,
  },

  modalHandlers: {
    [FOCUS_MODAL_ID]: handleFocusModalSubmit,
  },
};
