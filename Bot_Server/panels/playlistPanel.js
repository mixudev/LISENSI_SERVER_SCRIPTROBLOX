/**
 * playlistPanel.js — Panel Playlist Manager.
 *
 * Channel: PLAYLIST_CHANNEL_ID
 * Tombol:
 *   🎵 Tambah Lagu — Tambah video/musik YouTube ke playlist pribadi
 *   📋 Lihat Playlist — Lihat daftar lagu di playlist pribadi
 *   ▶️ Putar Playlist — Putar seluruh playlist di Voice Channel saat ini
 *   🗑️ Hapus Lagu — Hapus lagu tertentu dari playlist pribadi
 */

const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../config');

const BUTTON_IDS = {
  ADD: 'pl_btn_add',
  VIEW: 'pl_btn_view',
  PLAY: 'pl_btn_play',
  DELETE: 'pl_btn_delete',
};

function getPayload() {
  const embed = new EmbedBuilder()
    .setColor(0x9b59b6) // Purple
    .setTitle('Playlist Manager')
    .setDescription(
      [
        'Atur playlist lagu lofi Anda sendiri untuk didengarkan saat fokus atau bersantai.',
        'Lagu yang Anda tambahkan dapat diputar otomatis saat waktu istirahat tiba!',
        '',
        '**Fungsi Tombol:**',
        '- **Tambah Lagu** — Tambahkan lagu dari YouTube (maks 50 lagu).',
        '- **Lihat Playlist** — Tampilkan semua lagu yang terdaftar di playlist Anda.',
        '- **Putar Playlist** — Putar semua lagu playlist Anda di Voice Channel Anda.',
        '- **Hapus Lagu** — Pilih lagu yang ingin dihapus dari playlist Anda.',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Playlist Manager' })
    .setTimestamp();

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.ADD)
      .setLabel('Tambah Lagu')
      .setStyle(ButtonStyle.Primary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.VIEW)
      .setLabel('Lihat Playlist')
      .setStyle(ButtonStyle.Secondary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.PLAY)
      .setLabel('Putar Playlist')
      .setStyle(ButtonStyle.Success),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.DELETE)
      .setLabel('Hapus Lagu')
      .setStyle(ButtonStyle.Danger)
  );

  return { embeds: [embed], components: [row] };
}

// Lazy loaded handlers
const handlePlaylistAdd = require('../interactions/buttons/playlist_add');
const handlePlaylistView = require('../interactions/buttons/playlist_view');
const handlePlaylistPlay = require('../interactions/buttons/playlist_play');
const handlePlaylistDelete = require('../interactions/buttons/playlist_delete');
const handlePlaylistModalSubmit = require('../interactions/modals/playlist_add_modal');
const { PL_ADD_MODAL_ID } = require('../interactions/buttons/playlist_add');
const handleSelectPlaylistDelete = require('../interactions/buttons/select_playlist_delete');
const { PL_SELECT_DELETE_ID } = require('../interactions/buttons/playlist_delete');

module.exports = {
  name: 'playlist',

  getChannelId: () => config.discord.playlistChannelId,

  getPayload,

  buttonHandlers: {
    [BUTTON_IDS.ADD]: handlePlaylistAdd,
    [BUTTON_IDS.VIEW]: handlePlaylistView,
    [BUTTON_IDS.PLAY]: handlePlaylistPlay,
    [BUTTON_IDS.DELETE]: handlePlaylistDelete,
  },

  modalHandlers: {
    [PL_ADD_MODAL_ID]: handlePlaylistModalSubmit,
  },

  selectMenuHandlers: {
    [PL_SELECT_DELETE_ID]: handleSelectPlaylistDelete,
  },
};
