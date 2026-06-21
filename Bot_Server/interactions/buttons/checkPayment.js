const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [🔍 Cek Pembayaran]
 * Memverifikasi pembayaran via Midtrans API di Laravel Backend.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleCheckPayment(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const channelId = interaction.channelId;

  // Hubungi Laravel untuk cek status pembayaran tiket
  const result = await laravelService.checkTicketPayment(channelId);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal menghubungi Laravel Backend untuk mengecek status pembayaran.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const paymentData = result.data;

  if (paymentData.paid) {
    // Pembayaran sukses! Update embed di channel.
    const key = paymentData.key;
    const user = interaction.user;

    const paidEmbed = new EmbedBuilder()
      .setColor(0x2ecc71)
      .setTitle('✅ Pembayaran Lunas & Lisensi Diterbitkan')
      .setDescription(
        [
          `Terima kasih ${user}! Pembayaran Anda telah terkonfirmasi.`,
          '',
          `🔑 **License Key Anda:**`,
          `\`\`\`${key}\`\`\``,
          '',
          '**Cara Aktivasi Lisensi:**',
          '1. Salin/Copy license key di atas.',
          '2. Masuk ke channel panel publik bot, lalu klik tombol **Redeem Key**.',
          '3. Masukkan license key tersebut untuk mengikat lisensi ke akun Discord Anda.',
          '',
          'Setelah mengaktifkan lisensi, Anda bisa menutup tiket ini menggunakan tombol di bawah.',
        ].join('\n')
      )
      .setFooter({ text: 'License System • Pembayaran Sukses' })
      .setTimestamp();

    // Hapus tombol Cek Pembayaran, sisakan Tutup Ticket saja
    const row = new ActionRowBuilder().addComponents(
      new ButtonBuilder()
        .setCustomId('tkt_close')
        .setLabel('Tutup Ticket')
        .setEmoji('🔒')
        .setStyle(ButtonStyle.Danger)
    );

    // Edit pesan invoice asli di channel
    try {
      await interaction.message.edit({
        embeds: [paidEmbed],
        components: [row]
      });
    } catch (editErr) {
      console.error('❌ Gagal mengedit pesan invoice:', editErr);
    }

    await interaction.editReply({
      embeds: [
        successEmbed(
          '💳 Pembayaran Terverifikasi',
          'Pembayaran Anda telah sukses diverifikasi! Lisensi Anda telah diterbitkan di dalam channel tiket ini.'
        ),
      ],
    });
  } else {
    // Belum bayar
    await interaction.editReply({
      embeds: [
        errorEmbed(
          paymentData.message || 'Pembayaran belum terdeteksi. Silakan bayar terlebih dahulu lalu klik Cek Pembayaran.'
        ),
      ],
    });
  }

  autoDeleteReply(interaction, 15000);
}

module.exports = handleCheckPayment;
