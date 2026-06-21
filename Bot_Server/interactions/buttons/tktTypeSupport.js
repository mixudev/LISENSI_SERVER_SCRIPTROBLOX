const { ChannelType, PermissionFlagsBits, EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../../config');
const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler tombol [🐞 Tanya Teknis / Bug Report]
 * Membuat channel privat tiket bantuan tipe support.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleTktTypeSupport(interaction) {
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

  // 2. Hubungi Laravel untuk menyimpan data tiket tipe 'support'
  const result = await laravelService.createTicket(user.id, channel.id, 'support');

  if (!result.success) {
    // Jika gagal, hapus kembali channelnya
    try {
      await channel.delete();
    } catch (_) {}

    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal membuat tiket di database.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  // 3. Kirim panel kontrol tiket ke channel baru
  const ticketEmbed = new EmbedBuilder()
    .setColor(0x3498db)
    .setTitle(`🐞 Tiket Dukungan Teknis — ${user.username}`)
    .setDescription(
      [
        `Halo ${user}, selamat datang di tiket bantuan teknis Anda.`,
        'Silakan jelaskan kendala atau bug yang Anda hadapi secara detail.',
        'Staff Admin kami akan segera melayani Anda.',
      ].join('\n')
    )
    .setFooter({ text: 'License System • Tiket Teknis' })
    .setTimestamp();

  const ticketRow = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId('tkt_process')
      .setLabel('Proses Ticket')
      .setEmoji('🔄')
      .setStyle(ButtonStyle.Primary),
    new ButtonBuilder()
      .setCustomId('tkt_generate_key')
      .setLabel('Generate Key')
      .setEmoji('🔑')
      .setStyle(ButtonStyle.Success),
    new ButtonBuilder()
      .setCustomId('tkt_close')
      .setLabel('Tutup Ticket')
      .setEmoji('🔒')
      .setStyle(ButtonStyle.Danger)
  );

  await channel.send({
    content: `${user} | <@&${config.discord.adminRoleId}>`,
    embeds: [ticketEmbed],
    components: [ticketRow],
  });

  // 4. Konfirmasi sukses ke user secara ephemeral
  await interaction.editReply({
    embeds: [
      successEmbed(
        '🎫 Tiket Dukungan Berhasil Dibuat',
        `Channel tiket Anda telah berhasil dibuat: ${channel}\nSilakan masuk ke channel tersebut untuk memulai percakapan.`
      ),
    ],
  });

  autoDeleteReply(interaction, 15000);
}

module.exports = handleTktTypeSupport;
