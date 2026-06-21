const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');

/**
 * Handler tombol [🎫 Open Ticket] — Ticket Panel.
 * Menampilkan pilihan tipe tiket secara ephemeral.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleOpenTicket(interaction) {
  const embed = new EmbedBuilder()
    .setColor(0x5865f2)
    .setTitle('🎫 Pilih Tipe Tiket')
    .setDescription(
      [
        'Silakan pilih tipe tiket yang ingin Anda buat:',
        '',
        '🐞 **Tanya Teknis / Bug Report**',
        'Pilih ini jika Anda mengalami kendala teknis, error script, atau ingin melaporkan bug.',
        '',
        '💳 **Pembelian Lisensi**',
        'Pilih ini jika Anda ingin membeli lisensi baru. Sistem akan otomatis menerbitkan invoice QRIS Midtrans di channel tiket Anda agar bisa langsung dibayar.',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Pilihan Tiket' });

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId('tkt_type_support')
      .setLabel('Tanya Teknis / Bug Report')
      .setEmoji('🐞')
      .setStyle(ButtonStyle.Secondary),
    new ButtonBuilder()
      .setCustomId('tkt_type_purchase')
      .setLabel('Pembelian Lisensi')
      .setEmoji('💳')
      .setStyle(ButtonStyle.Success)
  );

  await interaction.reply({
    embeds: [embed],
    components: [row],
    ephemeral: true,
  });
}

module.exports = handleOpenTicket;
