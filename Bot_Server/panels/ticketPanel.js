/**
 * Ticket Panel — Dashboard untuk membuat/membuka tiket bantuan.
 *
 * Channel: TICKET_CHANNEL_ID
 * Tombol:
 *   🎫 Open Ticket — Membuat channel tiket bantuan private
 *
 * Cara daftarkan: panelManager.register(require('./panels/ticketPanel'))
 */

const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../config');

const BUTTON_IDS = {
  OPEN_TICKET: 'pub_open_ticket',
};

function getPayload() {
  const embed = new EmbedBuilder()
    .setColor(0x5865f2)
    .setTitle('🎫 Customer Support Tickets')
    .setDescription(
      [
        'Butuh bantuan, mengalami kendala teknis, atau ingin membeli lisensi?',
        'Klik tombol di bawah untuk membuka tiket dukungan baru.',
        '',
        '👉 **Open Ticket** — Membuat channel privat yang hanya bisa diakses oleh Anda dan Staff Admin kami.',
        '',
        'Staff kami akan segera membantu Anda di channel tiket yang dibuat.',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Support Center' })
    .setTimestamp();

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.OPEN_TICKET)
      .setLabel('Open Ticket')
      .setEmoji('🎫')
      .setStyle(ButtonStyle.Primary)
  );

  return { embeds: [embed], components: [row] };
}

const handleOpenTicket = require('../interactions/buttons/openTicket');
const handleTktTypeSupport = require('../interactions/buttons/tktTypeSupport');
const handleTktTypePurchase = require('../interactions/buttons/tktTypePurchase');
const handleCheckPayment = require('../interactions/buttons/checkPayment');
const handleProcessTicket = require('../interactions/buttons/processTicket');
const handleGenerateKeyFromTicket = require('../interactions/buttons/generateKeyFromTicket');
const handleCloseTicket = require('../interactions/buttons/closeTicket');

module.exports = {
  name: 'ticket',

  /** Channel tempat panel ini dikirim */
  getChannelId: () => config.discord.ticketChannelId,

  /** Payload embed + tombol */
  getPayload,

  /** Map customId → handler function */
  buttonHandlers: {
    [BUTTON_IDS.OPEN_TICKET]: handleOpenTicket,
    tkt_type_support: handleTktTypeSupport,
    tkt_type_purchase: handleTktTypePurchase,
    tkt_check_payment: handleCheckPayment,
    tkt_process: handleProcessTicket,
    tkt_generate_key: handleGenerateKeyFromTicket,
    tkt_close: handleCloseTicket,
  },
};
