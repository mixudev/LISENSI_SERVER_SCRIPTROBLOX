const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');

/**
 * customId tombol dipusatkan di sini agar konsisten dipakai
 * baik saat membuat tombol maupun saat routing interaksi di bot.js.
 */
const BUTTON_IDS = {
  GENERATE_KEY: 'dashboard_generate_key',
  RESET_HWID: 'dashboard_reset_hwid',
  GET_SCRIPT: 'dashboard_get_script',
  GET_STATS: 'dashboard_get_stats',
};

/**
 * Membangun embed utama Dashboard.
 * Dipisah jadi fungsi sendiri supaya mudah diubah tampilannya
 * tanpa menyentuh logika pengiriman pesan.
 */
function buildDashboardEmbed() {
  return new EmbedBuilder()
    .setColor(0x5865f2)
    .setTitle('🛡️ License Management Dashboard')
    .setDescription(
      [
        'Gunakan tombol di bawah ini untuk mengelola lisensi software Anda.',
        'Semua respon bersifat **privat** dan hanya terlihat oleh Anda.',
        '',
        '🔑 **Generate Key** — Khusus Admin/Reseller, membuat key baru.',
        '🔄 **Reset HWID** — Membuka kunci HWID pada lisensi Anda.',
        '📜 **Get Script** — Mengambil script loader terbaru.',
        '📊 **Get Stats** — Melihat status & detail lisensi Anda.',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Dashboard diperbarui otomatis oleh bot' })
    .setTimestamp();
}

/**
 * Membangun ActionRow berisi 4 tombol dashboard.
 */
function buildDashboardButtons() {
  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId(BUTTON_IDS.GENERATE_KEY)
      .setLabel('Generate Key')
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

  return row;
}

/**
 * Payload siap pakai untuk channel.send() atau message.edit()
 */
function getDashboardPayload() {
  return {
    embeds: [buildDashboardEmbed()],
    components: [buildDashboardButtons()],
  };
}

module.exports = {
  BUTTON_IDS,
  buildDashboardEmbed,
  buildDashboardButtons,
  getDashboardPayload,
};
