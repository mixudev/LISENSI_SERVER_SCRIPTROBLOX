const laravelService = require('../../services/laravelService');
const { errorEmbed } = require('../../utils/replyHelper');

/**
 * Handler tombol [🔒 Tutup Ticket] — Tiket Channel.
 * Mengubah status tiket menjadi "closed" di database, lalu menghapus channel setelah 5 detik.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleCloseTicket(interaction) {
  await interaction.reply({
    content: '🔒 **Tiket sedang ditutup. Channel ini akan dihapus dalam 5 detik...**',
  });

  const channelId = interaction.channelId;
  const userId = interaction.user.id;

  // Hubungi Laravel untuk menyimpan status closed
  const result = await laravelService.closeTicket(channelId, userId);

  if (!result.success) {
    await interaction.followUp({
      embeds: [errorEmbed(result.message || 'Gagal menyimpan status penutupan tiket di database.')],
      ephemeral: true,
    });
    return;
  }

  // Tunggu 5 detik lalu hapus channel
  setTimeout(async () => {
    try {
      await interaction.channel.delete();
    } catch (err) {
      console.error('❌ Gagal menghapus channel tiket:', err.message);
    }
  }, 5000);
}

module.exports = handleCloseTicket;
