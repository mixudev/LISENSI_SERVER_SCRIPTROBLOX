const { ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder } = require('discord.js');
const config = require('../../config');
const { forbiddenEmbed } = require('../../utils/replyHelper');

/**
 * ID modal & input fields — didefinisikan di sini agar konsisten
 * antara handler tombol (yang membuka modal) dan handler submit modal.
 * Import dari file ini jika butuh ID-nya di tempat lain.
 */
const ADM_GENERATE_KEY_MODAL_ID = 'adm_modal_generate_key';
const ADM_TARGET_DISCORD_ID_INPUT = 'adm_input_target_discord_id';
const ADM_DURATION_INPUT_ID = 'adm_input_duration_days';

/**
 * Handler tombol [🔑 Generate Key] — Admin Panel.
 * Hanya bisa diakses oleh user yang:
 *   - Memiliki Role ADMIN_ROLE_ID, ATAU
 *   - User ID-nya ada di ADMIN_USER_IDS (.env Bot_Server)
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleGenerateKey(interaction) {
  // Pengecekan akses berlapis: Role Discord ATAU User ID individual
  const hasAdminRole = interaction.member.roles.cache.has(config.discord.adminRoleId);
  const isAdminUser  = config.discord.adminUserIds.includes(interaction.user.id);
  const isAdmin      = hasAdminRole || isAdminUser;

  if (!isAdmin) {
    await interaction.reply({
      embeds: [forbiddenEmbed('Hanya Admin/Reseller yang dapat menggunakan fitur Generate Key.')],
      ephemeral: true,
    });
    return;
  }

  const modal = new ModalBuilder()
    .setCustomId(ADM_GENERATE_KEY_MODAL_ID)
    .setTitle('Generate License Key');

  const targetInput = new TextInputBuilder()
    .setCustomId(ADM_TARGET_DISCORD_ID_INPUT)
    .setLabel('Discord User ID penerima lisensi')
    .setPlaceholder('Contoh: 111222333444555666')
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

module.exports = {
  handleGenerateKey,
  ADM_GENERATE_KEY_MODAL_ID,
  ADM_TARGET_DISCORD_ID_INPUT,
  ADM_DURATION_INPUT_ID,
};
