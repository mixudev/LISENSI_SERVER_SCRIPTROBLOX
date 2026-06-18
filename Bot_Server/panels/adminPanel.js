/**
 * Admin Panel — Dashboard khusus Admin/Reseller.
 *
 * Channel: ADMIN_CHANNEL_ID (isi di .env Bot_Server)
 * Tombol:
 *   🔑 Generate Key — Buat license key baru untuk user
 *
 * Cara tambah tombol baru:
 *  1. Tambah entry di BUTTON_IDS
 *  2. Buat handler di interactions/buttons/
 *  3. Tambah ke buttonHandlers di bawah
 *  4. Tambah ButtonBuilder di getPayload()
 *
 * Cara daftarkan: panelManager.register(require('./panels/adminPanel'))
 */

const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../config');

// Prefix "adm_" untuk menghindari konflik customId dengan panel lain
const BUTTON_IDS = {
  GENERATE_KEY: 'adm_generate_key',
  // Tambah tombol admin baru di sini:
  // REVOKE_KEY:   'adm_revoke_key',
  // LIST_USERS:   'adm_list_users',
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
      .setStyle(ButtonStyle.Danger)
    // Tambah tombol admin baru di sini, contoh:
    // new ButtonBuilder()
    //   .setCustomId(BUTTON_IDS.REVOKE_KEY)
    //   .setLabel('Revoke Key')
    //   .setEmoji('🚫')
    //   .setStyle(ButtonStyle.Danger),
  );

  return { embeds: [embed], components: [row] };
}

// Handler tombol & modal
const { handleGenerateKey, ADM_GENERATE_KEY_MODAL_ID } = require('../interactions/buttons/generateKey');
const handleGenerateKeyModalSubmit = require('../interactions/modals/generateKeyModal');

module.exports = {
  name: 'admin',

  /** Channel tempat panel ini dikirim */
  getChannelId: () => config.discord.adminChannelId,

  /** Payload embed + tombol */
  getPayload,

  /** Map customId → handler function */
  buttonHandlers: {
    [BUTTON_IDS.GENERATE_KEY]: handleGenerateKey,
  },

  /** Map customId modal → handler function */
  modalHandlers: {
    [ADM_GENERATE_KEY_MODAL_ID]: handleGenerateKeyModalSubmit,
  },
};
