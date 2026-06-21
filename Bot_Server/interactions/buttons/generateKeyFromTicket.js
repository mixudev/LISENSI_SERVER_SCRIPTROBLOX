const { ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder } = require('discord.js');
const config = require('../../config');
const laravelService = require('../../services/laravelService');
const { errorEmbed, forbiddenEmbed } = require('../../utils/replyHelper');
const { ADM_GENERATE_KEY_MODAL_ID, ADM_TARGET_DISCORD_ID_INPUT, ADM_DURATION_INPUT_ID } = require('./generateKey');

/**
 * Handler tombol [🔑 Generate Key] — Tiket Channel.
 * Membuka modal generate key dengan Discord ID target terisi otomatis sesuai pembuat tiket.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleGenerateKeyFromTicket(interaction) {
  const hasAdminRole = interaction.member.roles.cache.has(config.discord.adminRoleId);
  const isAdminUser  = config.discord.adminUserIds.includes(interaction.user.id);

  if (!hasAdminRole && !isAdminUser) {
    await interaction.reply({
      embeds: [forbiddenEmbed('Hanya Staff Admin yang dapat men-generate key.')],
      ephemeral: true,
    });
    return;
  }

  const channelId = interaction.channelId;

  // Dapatkan detail tiket untuk mencari Discord ID pembuat tiket
  const result = await laravelService.getTicket(channelId);

  if (!result.success || !result.data?.data?.discord_id) {
    await interaction.reply({
      embeds: [errorEmbed(result.message || 'Gagal mengambil data pembuat tiket.')],
      ephemeral: true,
    });
    return;
  }

  const ticketCreatorDiscordId = result.data.data.discord_id;

  const modal = new ModalBuilder()
    .setCustomId(ADM_GENERATE_KEY_MODAL_ID)
    .setTitle('Generate Key untuk Tiket');

  const targetInput = new TextInputBuilder()
    .setCustomId(ADM_TARGET_DISCORD_ID_INPUT)
    .setLabel('Discord User ID penerima lisensi')
    .setValue(ticketCreatorDiscordId)
    .setStyle(TextInputStyle.Short)
    .setMinLength(17)
    .setMaxLength(20)
    .setRequired(true);

  const durationInput = new TextInputBuilder()
    .setCustomId(ADM_DURATION_INPUT_ID)
    .setLabel('Durasi (hari) — isi 0 untuk Lifetime')
    .setPlaceholder('Contoh: 30')
    .setStyle(TextInputStyle.Short)
    .setMinLength(1)
    .setMaxLength(4)
    .setRequired(true);

  modal.addComponents(
    new ActionRowBuilder().addComponents(targetInput),
    new ActionRowBuilder().addComponents(durationInput)
  );

  await interaction.showModal(modal);
}

module.exports = handleGenerateKeyFromTicket;
