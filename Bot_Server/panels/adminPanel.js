/**
 * Admin Panel — Dashboard khusus Admin/Reseller.
 *
 * Channel: ADMIN_CHANNEL_ID (isi di .env Bot_Server)
 * Tombol:
 *   🔑 Generate Key — Buat license key baru untuk user
 *   👥 List Users   — Lihat daftar user dengan lisensi aktif
 *   🚫 Revoke Key   — Cabut lisensi Discord user
 *   📢 Broadcast    — Kirim pesan broadcast ke seluruh user via DM
 *   📊 Server Stats — Statistik total lisensi server
 *
 * Cara daftarkan: panelManager.register(require('./panels/adminPanel'))
 */

const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../config');

const BUTTON_IDS = {
  GENERATE_KEY: 'adm_generate_key',
  LIST_USERS:   'adm_list_users',
  REVOKE_KEY:   'adm_revoke_key',
  BROADCAST:    'adm_broadcast',
  SERVER_STATS: 'adm_server_stats',
};

function getPayload() {
  const embed = new EmbedBuilder()
    .setColor(0xed4245) // Merah Discord — menandakan panel admin
    .setTitle('⚙️ Admin Control Panel')
    .setDescription(
      [
        '**Panel khusus Admin/Reseller.** Akses terbatas.',
        '',
        '🔑 **Generate Key** — Buat license key baru untuk Discord User ID tertentu.',
        '👥 **List Users** — Tampilkan 25 lisensi aktif terbaru.',
        '🚫 **Revoke Key** — Cabut lisensi user berdasarkan Discord ID.',
        '📢 **Broadcast** — Kirim DM massal ke seluruh pemegang lisensi aktif.',
        '📊 **Server Stats** — Lihat statistik lisensi terdaftar di server.',
        '',
        '_Semua tindakan tercatat. Gunakan dengan bijak._',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Admin Panel' })
    .setTimestamp();

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.GENERATE_KEY)
      .setLabel('Generate Key')
      .setEmoji('🔑')
      .setStyle(ButtonStyle.Danger),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.LIST_USERS)
      .setLabel('List Users')
      .setEmoji('👥')
      .setStyle(ButtonStyle.Primary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.REVOKE_KEY)
      .setLabel('Revoke Key')
      .setEmoji('🚫')
      .setStyle(ButtonStyle.Danger),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.BROADCAST)
      .setLabel('Broadcast')
      .setEmoji('📢')
      .setStyle(ButtonStyle.Secondary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.SERVER_STATS)
      .setLabel('Server Stats')
      .setEmoji('📊')
      .setStyle(ButtonStyle.Success)
  );

  return { embeds: [embed], components: [row] };
}

// Handler tombol & modal
const { handleGenerateKey, ADM_GENERATE_KEY_MODAL_ID } = require('../interactions/buttons/generateKey');
const handleGenerateKeyModalSubmit = require('../interactions/modals/generateKeyModal');

const handleListUsers = require('../interactions/buttons/listUsers');
const { handleRevokeKey, ADM_REVOKE_KEY_MODAL_ID } = require('../interactions/buttons/revokeKey');
const handleRevokeKeyModalSubmit = require('../interactions/modals/revokeKeyModal');
const { handleBroadcastMsg, ADM_BROADCAST_MODAL_ID } = require('../interactions/buttons/broadcastMsg');
const handleBroadcastModalSubmit = require('../interactions/modals/broadcastModal');
const handleServerStats = require('../interactions/buttons/serverStats');

module.exports = {
  name: 'admin',

  /** Channel tempat panel ini dikirim */
  getChannelId: () => config.discord.adminChannelId,

  /** Payload embed + tombol */
  getPayload,

  /** Map customId → handler function */
  buttonHandlers: {
    [BUTTON_IDS.GENERATE_KEY]: handleGenerateKey,
    [BUTTON_IDS.LIST_USERS]:   handleListUsers,
    [BUTTON_IDS.REVOKE_KEY]:   handleRevokeKey,
    [BUTTON_IDS.BROADCAST]:    handleBroadcastMsg,
    [BUTTON_IDS.SERVER_STATS]: handleServerStats,
  },

  /** Map customId modal → handler function */
  modalHandlers: {
    [ADM_GENERATE_KEY_MODAL_ID]: handleGenerateKeyModalSubmit,
    [ADM_REVOKE_KEY_MODAL_ID]:   handleRevokeKeyModalSubmit,
    [ADM_BROADCAST_MODAL_ID]:    handleBroadcastModalSubmit,
  },
};
