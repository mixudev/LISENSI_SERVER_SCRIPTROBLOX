/**
 * storage.js — Helper baca/tulis file JSON untuk persistensi data ringan.
 * Digunakan oleh reminderService dan playlistService.
 */

const fs   = require('fs');
const path = require('path');

const DATA_DIR = path.join(__dirname, '..', 'data');

/**
 * Pastikan direktori data ada.
 */
function ensureDataDir() {
  if (!fs.existsSync(DATA_DIR)) {
    fs.mkdirSync(DATA_DIR, { recursive: true });
  }
}

/**
 * Baca JSON dari file. Return nilai default jika file tidak ada.
 * @param {string} filename  Nama file (misal: 'reminders.json')
 * @param {*} defaultValue   Nilai default jika file belum ada
 * @returns {*}
 */
function readJson(filename, defaultValue = null) {
  ensureDataDir();
  const filePath = path.join(DATA_DIR, filename);

  if (!fs.existsSync(filePath)) {
    return defaultValue;
  }

  try {
    const raw = fs.readFileSync(filePath, 'utf8');
    return JSON.parse(raw);
  } catch (err) {
    console.error(`❌ [Storage] Gagal baca ${filename}:`, err.message);
    return defaultValue;
  }
}

/**
 * Tulis data ke file JSON.
 * @param {string} filename
 * @param {*} data
 */
function writeJson(filename, data) {
  ensureDataDir();
  const filePath = path.join(DATA_DIR, filename);

  try {
    fs.writeFileSync(filePath, JSON.stringify(data, null, 2), 'utf8');
    try {
      fs.chmodSync(filePath, 0o666);
    } catch (chmodErr) {
      // Ignore if chmod fails (e.g. windows local environment outside docker)
    }
  } catch (err) {
    console.error(`❌ [Storage] Gagal tulis ${filename}:`, err.message);
  }
}

module.exports = { readJson, writeJson };
