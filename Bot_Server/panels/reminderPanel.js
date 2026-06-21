/**
 * reminderPanel.js — Panel Sistem Pengingat (Reminder).
 *
 * Channel: REMINDER_CHANNEL_ID
 * Tombol:
 *   ⏰ Tambah Pengingat — Buka modal tambah reminder
 *   📋 Daftar Pengingat  — Lihat pengingat aktif milik user
 *   🗑️ Hapus Pengingat   — Hapus pengingat yang dipilih
 */

const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../config');

const BUTTON_IDS = {
  ADD:    'rem_btn_add',
  LIST:   'rem_btn_list',
  DELETE: 'rem_btn_delete',
};

function getPayload() {
  const embed = new EmbedBuilder()
    .setColor(0x5865f2) // Blurple Discord
    .setTitle('Sistem Pengingat Cerdas (Smart Reminder)')
    .setDescription(
      [
        'Selamat datang di panel pengingat! Di sini Anda dapat mengatur pengingat pribadi atau pengingat yang men-tag role tertentu.',
        '',
        '**Fungsi Tombol:**',
        '- **Tambah Pengingat** — Mengatur pengingat baru via modal UI.',
        '- **Daftar Pengingat** — Menampilkan daftar pengingat aktif Anda.',
        '- **Hapus Pengingat** — Menghapus pengingat aktif pilihan Anda.',
        '',
        '**Command Alternatif:**',
        '- `/remindme [durasi] [pesan]` atau `!remind [mention/role] [durasi] [pesan]`',
        '- Contoh: `/remindme 2h Mabar Roblox` atau `!remind @user 1 day Turnamen ML`',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Reminder Panel' })
    .setTimestamp();

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.ADD)
      .setLabel('Tambah Pengingat')
      .setStyle(ButtonStyle.Primary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.LIST)
      .setLabel('Daftar Pengingat Saya')
      .setStyle(ButtonStyle.Secondary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.DELETE)
      .setLabel('Hapus Pengingat')
      .setStyle(ButtonStyle.Danger)
  );

  return { embeds: [embed], components: [row] };
}

// Lazy loaded handlers to prevent circular dependencies
const handleReminderAdd    = require('../interactions/buttons/reminder_add');
const handleReminderList   = require('../interactions/buttons/reminder_list');
const handleReminderDelete = require('../interactions/buttons/reminder_delete');
const handleReminderModalSubmit = require('../interactions/modals/reminder_modal');
const { REM_ADD_MODAL_ID } = require('../interactions/buttons/reminder_add');
const handleSelectReminderDelete = require('../interactions/buttons/select_reminder_delete');
const { REM_SELECT_DELETE_ID } = require('../interactions/buttons/reminder_delete');

module.exports = {
  name: 'reminder',

  getChannelId: () => config.discord.reminderChannelId,

  getPayload,

  buttonHandlers: {
    [BUTTON_IDS.ADD]:    handleReminderAdd,
    [BUTTON_IDS.LIST]:   handleReminderList,
    [BUTTON_IDS.DELETE]: handleReminderDelete,
  },

  modalHandlers: {
    [REM_ADD_MODAL_ID]: handleReminderModalSubmit,
  },

  selectMenuHandlers: {
    [REM_SELECT_DELETE_ID]: handleSelectReminderDelete,
  },
};
