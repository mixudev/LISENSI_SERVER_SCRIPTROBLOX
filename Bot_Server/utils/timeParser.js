/**
 * timeParser.js — Parse string waktu manusia ke Date.
 *
 * Contoh yang didukung:
 *   "30m"          → 30 menit dari sekarang
 *   "2h"           → 2 jam
 *   "1d"           → 1 hari
 *   "1 hour"       → 1 jam
 *   "3 days"       → 3 hari
 *   "2 hours 30m"  → 2.5 jam
 */

const UNIT_MAP = {
  s: 1000,
  sec: 1000,
  second: 1000,
  seconds: 1000,
  m: 60_000,
  min: 60_000,
  minute: 60_000,
  minutes: 60_000,
  menit: 60_000,
  h: 3_600_000,
  hr: 3_600_000,
  hour: 3_600_000,
  hours: 3_600_000,
  jam: 3_600_000,
  d: 86_400_000,
  day: 86_400_000,
  days: 86_400_000,
  hari: 86_400_000,
  w: 604_800_000,
  week: 604_800_000,
  weeks: 604_800_000,
  minggu: 604_800_000,
};

/**
 * Parse string waktu ke milliseconds.
 * @param {string} input  Contoh: "2h", "30 minutes", "1 day 2h"
 * @returns {number|null} Milliseconds atau null jika tidak bisa di-parse
 */
function parseToMs(input) {
  if (!input || typeof input !== 'string') return null;

  const str = input.trim().toLowerCase();
  const regex = /(\d+(?:\.\d+)?)\s*([a-z]+)/g;
  let total = 0;
  let matched = false;

  let m;
  while ((m = regex.exec(str)) !== null) {
    const num  = parseFloat(m[1]);
    const unit = m[2];

    if (UNIT_MAP[unit]) {
      total += num * UNIT_MAP[unit];
      matched = true;
    }
  }

  return matched ? total : null;
}

/**
 * Parse string waktu ke Date di masa depan.
 * @param {string} input
 * @returns {{ date: Date, ms: number }|null}
 */
function parseToDate(input) {
  const ms = parseToMs(input);
  if (!ms || ms <= 0) return null;

  return {
    date: new Date(Date.now() + ms),
    ms,
  };
}

/**
 * Format milliseconds ke string yang bisa dibaca manusia.
 * @param {number} ms
 * @returns {string} Contoh: "2 jam 30 menit"
 */
function formatDuration(ms) {
  if (!ms || ms <= 0) return '0 detik';

  const parts = [];
  const days  = Math.floor(ms / 86_400_000); ms %= 86_400_000;
  const hours = Math.floor(ms / 3_600_000);  ms %= 3_600_000;
  const mins  = Math.floor(ms / 60_000);     ms %= 60_000;
  const secs  = Math.floor(ms / 1_000);

  if (days)  parts.push(`${days} hari`);
  if (hours) parts.push(`${hours} jam`);
  if (mins)  parts.push(`${mins} menit`);
  if (secs && !days && !hours) parts.push(`${secs} detik`);

  return parts.join(' ') || '0 detik';
}

/**
 * Format Date ke string lokal Indonesia.
 * @param {Date} date
 * @returns {string}
 */
function formatDate(date) {
  return new Date(date).toLocaleString('id-ID', {
    timeZone: 'Asia/Jakarta',
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

module.exports = { parseToMs, parseTime: parseToMs, parseToDate, formatDuration, formatDate };
