const { EmbedBuilder } = require('discord.js');

/**
 * Embed standar untuk pesan error (warna merah).
 */
function errorEmbed(message) {
  return new EmbedBuilder()
    .setColor(0xed4245)
    .setDescription(message)
    .setTimestamp();
}

/**
 * Embed standar untuk pesan sukses (warna hijau).
 */
function successEmbed(title, message) {
  const embed = new EmbedBuilder()
    .setColor(0x57f287)
    .setTimestamp();

  if (message) {
    embed.setTitle(title);
    embed.setDescription(message);
  } else {
    // Jika dipanggil dengan 1 argument, anggap itu pesan utama tanpa judul
    embed.setDescription(title);
  }

  return embed;
}

/**
 * Embed standar untuk akses ditolak (warna oranye).
 */
function forbiddenEmbed(message) {
  return new EmbedBuilder()
    .setColor(0xfee75c)
    .setDescription(message || 'Anda tidak memiliki izin untuk menggunakan fitur ini.')
    .setTimestamp();
}

/**
 * Hapus reply interaksi secara otomatis setelah delayMs milidetik.
 * Digunakan agar respon ephemeral tidak numpuk di view user.
 *
 * @param {import('discord.js').Interaction} interaction
 * @param {number} [delayMs=20000] - Waktu sebelum dihapus (default: 20 detik)
 */
function autoDeleteReply(interaction, delayMs = 5000) {
  setTimeout(() => {
    interaction.deleteReply().catch(() => {
      // Abaikan jika sudah terhapus atau token expired
    });
  }, delayMs);
}

module.exports = {
  errorEmbed,
  successEmbed,
  forbiddenEmbed,
  autoDeleteReply,
};

