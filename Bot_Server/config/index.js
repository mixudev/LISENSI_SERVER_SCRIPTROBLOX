require('dotenv').config();

/**
 * Daftar environment variable yang WAJIB ada.
 * Jika salah satu tidak ditemukan, bot akan langsung berhenti (fail-fast)
 * agar tidak terjadi error tak terduga saat runtime.
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
    // User ID individual yang diizinkan Generate Key (tanpa perlu Role)
    adminUserIds: (process.env.ADMIN_USER_IDS || '')
      .split(',')
      .map((id) => id.trim())
      .filter(Boolean),
    // Channel khusus Admin Panel (opsional — jika kosong, panel admin dilewati)
    adminChannelId: process.env.ADMIN_CHANNEL_ID || null,
  },
  laravel: {
    apiUrl: process.env.LARAVEL_API_URL.replace(/\/+$/, ''), // hapus trailing slash
    apiToken: process.env.LARAVEL_API_TOKEN,
    timeout: Number(process.env.LARAVEL_API_TIMEOUT) || 8000,
  },
};
