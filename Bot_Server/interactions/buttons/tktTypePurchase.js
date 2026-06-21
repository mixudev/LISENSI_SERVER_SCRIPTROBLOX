const { ChannelType, PermissionFlagsBits, EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../../config');
const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [💳 Pembelian Lisensi]
 * Membuat channel privat tiket tipe purchase & menampilkan QRIS invoice.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleTktTypePurchase(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const guild = interaction.guild;
  const user = interaction.user;

  if (!config.discord.ticketCategoryId) {
    await interaction.editReply({
      embeds: [errorEmbed('Sistem tiket belum sepenuhnya dikonfigurasi oleh Admin (TICKET_CATEGORY_ID kosong).')],
    });
    autoDeleteReply(interaction);
    return;
  }

  // Cek apakah category exists
  const category = guild.channels.cache.get(config.discord.ticketCategoryId);
  if (!category || category.type !== ChannelType.GuildCategory) {
    await interaction.editReply({
      embeds: [errorEmbed('Kategori tiket tidak ditemukan di server Discord ini.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  let channel;
  try {
    const randomId = Math.floor(10000 + Math.random() * 90000); // 5 digit acak
    // 1. Buat private channel
    channel = await guild.channels.create({
      name: `ticket-${randomId}`,
      type: ChannelType.GuildText,
      parent: config.discord.ticketCategoryId,
      permissionOverwrites: [
        {
          id: guild.roles.everyone.id,
          deny: [PermissionFlagsBits.ViewChannel],
        },
        {
          id: interaction.client.user.id,
          allow: [
            PermissionFlagsBits.ViewChannel,
            PermissionFlagsBits.SendMessages,
            PermissionFlagsBits.ReadMessageHistory,
            PermissionFlagsBits.AttachFiles,
          ],
        },
        {
          id: user.id,
          allow: [
            PermissionFlagsBits.ViewChannel,
            PermissionFlagsBits.SendMessages,
            PermissionFlagsBits.ReadMessageHistory,
            PermissionFlagsBits.AttachFiles,
          ],
        },
        {
          id: config.discord.adminRoleId,
          allow: [
            PermissionFlagsBits.ViewChannel,
            PermissionFlagsBits.SendMessages,
            PermissionFlagsBits.ReadMessageHistory,
            PermissionFlagsBits.AttachFiles,
          ],
        },
      ],
    });
  } catch (err) {
    console.error('❌ Gagal membuat channel Discord privat:', err);
    await interaction.editReply({
      embeds: [errorEmbed('Gagal membuat channel tiket. Pastikan bot memiliki permission "Manage Channels".')],
    });
    autoDeleteReply(interaction);
    return;
  }

  // 2. Hubungi Laravel untuk menyimpan data tiket tipe 'purchase' (sekaligus generate QRIS)
  const result = await laravelService.createTicket(user.id, channel.id, 'purchase');

  if (!result.success) {
    // Jika gagal, hapus kembali channelnya
    try {
      await channel.delete();
    } catch (_) {}

    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal membuat tiket pembelian di database.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const ticketData = result.data.data;
  const price = ticketData.payment_amount || 50000;
  const formattedPrice = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price);

  const qrUrl = ticketData.payment_qr_url;

  // 3. Kirim panel kontrol tiket + invoice QRIS ke channel baru
  const invoiceEmbed = new EmbedBuilder()
    .setColor(0x2ecc71)
    .setTitle('💳 Invoice Pembelian Lisensi')
    .setDescription(
      [
        `Halo ${user}, terima kasih telah memilih layanan kami.`,
        'Berikut adalah rincian invoice pembelian lisensi Anda:',
        '',
        `💵 **Jumlah Tagihan:** \`${formattedPrice}\``,
        '📱 **Metode Pembayaran:** QRIS (Scan menggunakan GoPay, OVO, Dana, LinkAja, atau Mobile Banking)',
        '',
        '**Instruksi Pembayaran:**',
        '1. Scan kode QR di bawah menggunakan aplikasi pembayaran favorit Anda.',
        '2. Lakukan transfer sesuai nominal tagihan.',
        '3. Setelah transfer berhasil, klik tombol **🔍 Cek Pembayaran** di bawah.',
        '4. Sistem akan memverifikasi pembayaran Anda secara real-time dan otomatis menerbitkan lisensi key Anda.',
        '',
        'Jika ada kendala pembayaran, silakan hubungi Admin dengan mem-ping mereka di sini.',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Automated Invoice' })
    .setTimestamp();

  if (qrUrl) {
    invoiceEmbed.setImage(qrUrl);
  } else {
    invoiceEmbed.addFields({
      name: '⚠️ QR Code Tidak Tersedia',
      value: 'Gagal membuat kode QR pembayaran. Silakan hubungi staff Admin.',
    });
  }

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId('tkt_check_payment')
      .setLabel('Cek Pembayaran')
      .setEmoji('🔍')
      .setStyle(ButtonStyle.Success),
    new ButtonBuilder()
      .setCustomId('tkt_process')
      .setLabel('Proses Ticket')
      .setEmoji('🔄')
      .setStyle(ButtonStyle.Primary),
    new ButtonBuilder()
      .setCustomId('tkt_close')
      .setLabel('Tutup Ticket')
      .setEmoji('🔒')
      .setStyle(ButtonStyle.Danger)
  );

  await channel.send({
    content: `${user} | <@&${config.discord.adminRoleId}>`,
    embeds: [invoiceEmbed],
    components: [row],
  });

  // 4. Konfirmasi sukses ke user secara ephemeral
  await interaction.editReply({
    embeds: [
      successEmbed(
        '💳 Tiket Pembelian Dibuat',
        `Channel tiket pembelian Anda telah dibuat: ${channel}\nSilakan masuk ke channel tersebut untuk melakukan scan QRIS dan verifikasi pembayaran.`
      ),
    ],
  });

  autoDeleteReply(interaction, 15000);
}

module.exports = handleTktTypePurchase;
