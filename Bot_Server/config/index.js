require('dotenv').config();

/**
 * Daftar environment variable yang WAJIB ada.
 */
const REQUIRED_ENV_VARS = [
  'DISCORD_TOKEN',
  'DASHBOARD_CHANNEL_ID',
  'ADMIN_ROLE_ID',
  'LARAVEL_API_URL',
  'LARAVEL_API_TOKEN',
];

function validateEnv() {
  const missing = REQUIRED_ENV_VARS.filter((key) => !process.env[key]);

  if (missing.length > 0) {
    console.error('❌ Konfigurasi .env tidak lengkap. Variable berikut belum diisi:');
    missing.forEach((key) => console.error(`   - ${key}`));
    process.exit(1);
  }
}

validateEnv();

module.exports = {
  discord: {
    token: process.env.DISCORD_TOKEN,
    clientId: process.env.CLIENT_ID,
    dashboardChannelId: process.env.DASHBOARD_CHANNEL_ID,
    adminRoleId: process.env.ADMIN_ROLE_ID,
    adminUserIds: (process.env.ADMIN_USER_IDS || '')
      .split(',')
      .map((id) => id.trim())
      .filter(Boolean),
    adminChannelId: process.env.ADMIN_CHANNEL_ID || null,
    robloxChannelId: process.env.ROBLOX_CHANNEL_ID || null,
    ticketCategoryId: process.env.TICKET_CATEGORY_ID || null,
    ticketChannelId: process.env.TICKET_CHANNEL_ID || null,
    // Fitur Reminder & Playlist
    reminderChannelId: process.env.REMINDER_CHANNEL_ID || null,
    playlistChannelId: process.env.PLAYLIST_CHANNEL_ID || null,
  },
  laravel: {
    apiUrl: process.env.LARAVEL_API_URL.replace(/\/+$/, ''),
    apiToken: process.env.LARAVEL_API_TOKEN,
    timeout: Number(process.env.LARAVEL_API_TIMEOUT) || 8000,
  },
};
