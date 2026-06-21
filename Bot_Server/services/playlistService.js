/**
 * playlistService.js — Manajemen playlist per-user.
 *
 * Playlist disimpan di data/playlists.json
 * Format:
 * {
 *   "userId_guildId": [
 *     { "title": "Lofi Study", "url": "https://...", "addedAt": "ISO" }
 *   ]
 * }
 */

const { readJson, writeJson } = require('../utils/storage');

const FILE     = 'playlists.json';
const MAX_SONGS = 50;

function load() {
  return readJson(FILE, {});
}

function save(data) {
  writeJson(FILE, data);
}

function getKey(userId, guildId) {
  return `${userId}_${guildId}`;
}

/**
 * Ambil playlist user.
 * @param {string} userId
 * @param {string} guildId
 * @returns {Array}
 */
function getPlaylist(userId, guildId) {
  const data = load();
  return data[getKey(userId, guildId)] || [];
}

/**
 * Tambah lagu ke playlist user.
 * @param {string} userId
 * @param {string} guildId
 * @param {{ title: string, url: string }} track
 * @returns {{ success: boolean, message: string, playlist: Array }}
 */
function addTrack(userId, guildId, track) {
  const data = load();
  const key  = getKey(userId, guildId);

  if (!data[key]) data[key] = [];

  if (data[key].length >= MAX_SONGS) {
    return {
      success: false,
      message: `Playlist kamu sudah penuh (maks ${MAX_SONGS} lagu).`,
      playlist: data[key],
    };
  }

  // Cek duplikat URL
  const isDuplicate = data[key].some((t) => t.url === track.url);
  if (isDuplicate) {
    return {
      success: false,
      message: `Lagu **${track.title}** sudah ada di playlist kamu.`,
      playlist: data[key],
    };
  }

  data[key].push({
    title:   track.title,
    url:     track.url,
    addedAt: new Date().toISOString(),
  });

  save(data);

  return {
    success: true,
    message: `Lagu **${track.title}** berhasil ditambahkan!`,
    playlist: data[key],
  };
}

/**
 * Hapus lagu dari playlist berdasarkan index (0-based).
 * @param {string} userId
 * @param {string} guildId
 * @param {number} index
 * @returns {{ success: boolean, message: string }}
 */
function removeTrack(userId, guildId, index) {
  const data = load();
  const key  = getKey(userId, guildId);

  if (!data[key] || !data[key][index]) {
    return { success: false, message: 'Lagu tidak ditemukan di playlist.' };
  }

  const removed = data[key].splice(index, 1)[0];
  save(data);

  return { success: true, message: `**${removed.title}** dihapus dari playlist.` };
}

/**
 * Hapus semua lagu dari playlist user.
 */
function clearPlaylist(userId, guildId) {
  const data = load();
  const key  = getKey(userId, guildId);
  data[key]  = [];
  save(data);
}

module.exports = { getPlaylist, addTrack, removeTrack, clearPlaylist };
