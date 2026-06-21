/**
 * Roblox Panel — Dashboard untuk mengaitkan akun Roblox ke Discord.
 *
 * Channel: ROBLOX_CHANNEL_ID
 * Tombol:
 *   🎮 Kaitkan Akun — Kaitkan akun Roblox
 *   ❌ Hapus Kaitan — Hapus kaitan akun Roblox
 *
 * Cara daftarkan: panelManager.register(require('./panels/robloxPanel'))
 */

const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../config');

const BUTTON_IDS = {
  LINK_ROBLOX:   'pub_link_roblox',
  UNLINK_ROBLOX: 'pub_unlink_roblox',
};

function getPayload() {
  const embed = new EmbedBuilder()
    .setColor(0xff5a00)
    .setTitle('🎮 Roblox Account Binding')
    .setDescription(
      [
        'Kaitkan akun Roblox Anda ke Discord untuk keamanan tambahan.',
        'Setelah dikaitkan, lisensi Anda hanya dapat digunakan di akun Roblox tersebut.',
        '',
        '👉 **Kaitkan Akun** — Menghubungkan akun Roblox Anda.',
        '👉 **Hapus Kaitan** — Menghapus kaitan akun Roblox Anda.',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Roblox Integration' })
    .setTimestamp();

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.LINK_ROBLOX)
      .setLabel('Kaitkan Akun')
      .setEmoji('🎮')
      .setStyle(ButtonStyle.Primary),
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.UNLINK_ROBLOX)
      .setLabel('Hapus Kaitan')
      .setEmoji('❌')
      .setStyle(ButtonStyle.Danger)
  );

  return { embeds: [embed], components: [row] };
}

const handleLinkRoblox = require('../interactions/buttons/linkRoblox');
const handleUnlinkRoblox = require('../interactions/buttons/unlinkRoblox');

module.exports = {
  name: 'roblox',

  /** Channel tempat panel ini dikirim */
  getChannelId: () => config.discord.robloxChannelId,

  /** Payload embed + tombol */
  getPayload,

  /** Map customId → handler function */
  buttonHandlers: {
    [BUTTON_IDS.LINK_ROBLOX]:   handleLinkRoblox,
    [BUTTON_IDS.UNLINK_ROBLOX]: handleUnlinkRoblox,
  },
};
