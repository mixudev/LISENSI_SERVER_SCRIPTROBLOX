/**
 * reminderService.js — Layanan manajemen pengingat.
 *
 * Flow:
 *  1. Reminder disimpan di data/reminders.json
 *  2. Saat bot start, semua reminder yang belum terkirim di-schedule ulang
 *  3. Saat waktu tiba, kirim DM ke user + mention di REMINDER_CHANNEL
 */

const { v4: uuidv4 } = require('uuid');
const { readJson, writeJson } = require('../utils/storage');
const { formatDate } = require('../utils/timeParser');

const FILE = 'reminders.json';

/** @type {import('discord.js').Client|null} */
let _client = null;

/** Map<reminderId, TimeoutHandle> */
const _timers = new Map();

// ─── Data Access ──────────────────────────────────────────────────────────────

function load() {
  return readJson(FILE, []);
}

function save(reminders) {
  writeJson(FILE, reminders);
}

// ─── Public API ───────────────────────────────────────────────────────────────

/**
 * Inisialisasi service dengan Discord client.
 * Harus dipanggil setelah client ready.
 * @param {import('discord.js').Client} client
 */
function init(client) {
  _client = client;
  scheduleAll();
  console.log('⏰ [Reminder] Service aktif, memuat reminder tersimpan...');
}

/**
 * Tambah reminder baru.
 * @param {Object} data
 * @param {string} data.userId
 * @param {string} data.guildId
 * @param {string} data.message
 * @param {Date}   data.fireAt
 * @param {string|null} [data.targetTag] — mention @user atau @role (opsional)
 * @returns {Object} Reminder yang baru dibuat
 */
function add({ userId, guildId, message, fireAt, targetTag = null }) {
  const reminders = load();

  const reminder = {
    id: uuidv4(),
    userId,
    guildId,
    message,
    targetTag,
    fireAt: new Date(fireAt).toISOString(),
    createdAt: new Date().toISOString(),
  };

  reminders.push(reminder);
  save(reminders);
  schedule(reminder);

  return reminder;
}

/**
 * Ambil semua reminder milik satu user di guild tertentu.
 * @param {string} userId
 * @param {string} guildId
 * @returns {Array}
 */
function getByUser(userId, guildId) {
  return load().filter((r) => r.userId === userId && r.guildId === guildId);
}

/**
 * Hapus reminder berdasarkan ID.
 * @param {string} id
 * @param {string} userId — pastikan hanya pemiliknya yang bisa hapus
 * @returns {boolean}
 */
function remove(id, userId) {
  const reminders = load();
  const idx = reminders.findIndex((r) => r.id === id && r.userId === userId);
  if (idx === -1) return false;

  reminders.splice(idx, 1);
  save(reminders);

  // Cancel timer jika ada
  if (_timers.has(id)) {
    clearTimeout(_timers.get(id));
    _timers.delete(id);
  }

  return true;
}

/**
 * Jadwalkan semua reminder yang belum terkirim (dipanggil saat bot start).
 */
function scheduleAll() {
  const reminders = load();
  const now = Date.now();
  let scheduled = 0;

  for (const reminder of reminders) {
    const fireAt = new Date(reminder.fireAt).getTime();
    if (fireAt > now) {
      schedule(reminder);
      scheduled++;
    } else {
      // Sudah lewat — kirim langsung dengan catatan terlambat
      fire(reminder, true);
    }
  }

  console.log(`⏰ [Reminder] ${scheduled} reminder dijadwalkan ulang.`);
}

/**
 * Set timeout untuk satu reminder.
 * @param {Object} reminder
 */
function schedule(reminder) {
  const delay = new Date(reminder.fireAt).getTime() - Date.now();
  if (delay <= 0) {
    fire(reminder);
    return;
  }

  const handle = setTimeout(() => fire(reminder), delay);
  _timers.set(reminder.id, handle);
}

/**
 * Kirim notifikasi reminder.
 * @param {Object} reminder
 * @param {boolean} [late=false]
 */
async function fire(reminder, late = false) {
  _timers.delete(reminder.id);

  // Hapus dari storage
  const reminders = load();
  const cleaned = reminders.filter((r) => r.id !== reminder.id);
  save(cleaned);

  if (!_client) return;

  const lateNote = late ? '\n> *(Pengingat ini terlambat terkirim karena bot sempat offline)*' : '';
  const timeStr  = formatDate(new Date(reminder.fireAt));
  const mention  = reminder.targetTag ? `${reminder.targetTag} ` : '';

  const text = [
    `⏰ **Pengingat!** ${mention}`,
    ``,
    `> 📝 **${reminder.message}**`,
    `> 🕐 Dijadwalkan: \`${timeStr}\``,
    lateNote,
  ].join('\n');

  try {
    // 1. Kirim DM ke user
    const user = await _client.users.fetch(reminder.userId);
    if (user) {
      await user.send(text).catch(() => null);
    }
  } catch (_) {}

  try {
    // 2. Kirim ke reminder channel
    const channel = await _client.channels.fetch(process.env.REMINDER_CHANNEL_ID);
    if (channel?.isTextBased()) {
      await channel.send(`<@${reminder.userId}> ${text}`);
    }
  } catch (err) {
    console.error('❌ [Reminder] Gagal kirim ke channel:', err.message);
  }
}

module.exports = { init, add, getByUser, remove };
