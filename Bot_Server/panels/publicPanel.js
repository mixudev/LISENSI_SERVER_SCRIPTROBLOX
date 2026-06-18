/**
 * Public Panel — Dashboard utama untuk semua user.
 *
 * Channel: DASHBOARD_CHANNEL_ID
 * Tombol:
 *   🔑 Get Key    — Tampilkan license key milik user
 *   🔄 Reset HWID — Reset HWID pada lisensi user
 *   📜 Get Script — Ambil script loader terbaru
 *   📊 Get Stats  — Lihat detail & status lisensi
 *
 * Cara daftarkan: panelManager.register(require('./panels/publicPanel'))
 */

const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../config');

// Prefix "pub_" untuk menghindari konflik customId dengan panel lain
const BUTTON_IDS = {
  GET_KEY:    'pub_get_key',
  RESET_HWID: 'pub_reset_hwid',
  GET_SCRIPT: 'pub_get_script',
  GET_STATS:  'pub_get_stats',
};

function getPayload() {
  const embed = new EmbedBuilder()
    .setColor(0x5865f2)
    .setTitle('🛡️ License Management Dashboard')
    .setDescription(
      [
        'Gunakan tombol di bawah untuk mengelola lisensi software Anda.',
        'Semua respon bersifat **privat** dan hanya terlihat oleh Anda.',
        '',
        ' **Get Key** — Tampilkan license key Anda.',
        ' **Reset HWID** — Membuka kunci HWID pada lisensi Anda.',
        ' **Get Script** — Mengambil script loader terbaru.',
        ' **Get Stats** — Melihat status & detail lisensi Anda.',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Dashboard diperbarui otomatis oleh bot' })
    .setTimestamp();

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.GET_KEY)
      .setLabel('Get Key')
      .setEmoji('🔑')
      .setStyle(ButtonStyle.Success),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.RESET_HWID)
      .setLabel('Reset HWID')
      .setEmoji('🔄')
      .setStyle(ButtonStyle.Primary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.GET_SCRIPT)
      .setLabel('Get Script')
      .setEmoji('📜')
      .setStyle(ButtonStyle.Secondary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.GET_STATS)
      .setLabel('Get Stats')
      .setEmoji('📊')
      .setStyle(ButtonStyle.Secondary)
  );

  return { embeds: [embed], components: [row] };
}

// Handler untuk setiap tombol
const handleGetKey    = require('../interactions/buttons/getKey');
const handleResetHwid = require('../interactions/buttons/resetHwid');
const handleGetScript = require('../interactions/buttons/getScript');
const handleGetStats  = require('../interactions/buttons/getStats');

module.exports = {
  name: 'public',

  /** Channel tempat panel ini dikirim */
  getChannelId: () => config.discord.dashboardChannelId,

  /** Payload embed + tombol */
  getPayload,

  /** Map customId → handler function */
  buttonHandlers: {
    [BUTTON_IDS.GET_KEY]:    handleGetKey,
    [BUTTON_IDS.RESET_HWID]: handleResetHwid,
    [BUTTON_IDS.GET_SCRIPT]: handleGetScript,
    [BUTTON_IDS.GET_STATS]:  handleGetStats,
  },

  /** Tidak ada modal di panel public */
  modalHandlers: {},
};
