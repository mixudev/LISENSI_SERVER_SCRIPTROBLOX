const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const config = require('../../config');
const laravelService = require('../../services/laravelService');
const { errorEmbed, forbiddenEmbed } = require('../../utils/replyHelper');

/**
 * Handler tombol [🔄 Proses Ticket] — Tiket Channel.
 * Mengubah status tiket menjadi "processing" di database.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleProcessTicket(interaction) {
  const hasAdminRole = interaction.member.roles.cache.has(config.discord.adminRoleId);
  const isAdminUser  = config.discord.adminUserIds.includes(interaction.user.id);

  if (!hasAdminRole && !isAdminUser) {
    await interaction.reply({
      embeds: [forbiddenEmbed('Hanya Staff Admin yang dapat memproses tiket ini.')],
      ephemeral: true,
    });
    return;
  }

  await interaction.deferUpdate();

  const channelId = interaction.channelId;
  const adminId = interaction.user.id;

  const result = await laravelService.processTicket(channelId, adminId);

  if (!result.success) {
    await interaction.followUp({
      embeds: [errorEmbed(result.message || 'Gagal memproses tiket ini.')],
      ephemeral: true,
    });
    return;
  }

  // Edit original embed & disable button Proses
  const originalEmbed = interaction.message.embeds[0];
  const newEmbed = EmbedBuilder.from(originalEmbed)
    .setColor(0xffa500) // Orange
    .setTitle(` Tiket Sedang Diproses — ${interaction.user.username}`)
    .setDescription(originalEmbed.description + `\n\n**Status**: Tiket sedang dilayani oleh ${interaction.user}.`);

  const originalRow = interaction.message.components[0];
  const newRow = ActionRowBuilder.from(originalRow);

  // Disable tombol proses
  newRow.components.forEach((btn) => {
    if (btn.data.custom_id === 'tkt_process') {
      btn.data.disabled = true;
      btn.data.label = 'Sedang Diproses';
    }
  });

  await interaction.message.edit({
    embeds: [newEmbed],
    components: [newRow],
  });

  await interaction.channel.send({
    content: ` **Tiket ini sekarang sedang dilayani oleh** ${interaction.user}.`,
  });
}

module.exports = handleProcessTicket;
