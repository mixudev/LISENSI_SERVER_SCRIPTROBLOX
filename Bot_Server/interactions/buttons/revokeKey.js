const { ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder } = require('discord.js');
const config = require('../../config');
const { forbiddenEmbed } = require('../../utils/replyHelper');

const ADM_REVOKE_KEY_MODAL_ID = 'adm_modal_revoke_key';
const ADM_REVOKE_TARGET_INPUT_ID = 'adm_input_revoke_target';

/**
 * Handler tombol [🚫 Revoke Key] — Admin Panel.
 * Membuka modal input Discord ID target untuk dicabut lisensinya.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleRevokeKey(interaction) {
  const hasAdminRole = interaction.member.roles.cache.has(config.discord.adminRoleId);
  const isAdminUser  = config.discord.adminUserIds.includes(interaction.user.id);

  if (!hasAdminRole && !isAdminUser) {
    await interaction.reply({
      embeds: [forbiddenEmbed('Hanya Staff Admin yang dapat mencabut lisensi.')],
      ephemeral: true,
    });
    return;
  }

  const modal = new ModalBuilder()
    .setCustomId(ADM_REVOKE_KEY_MODAL_ID)
    .setTitle('Cabut Lisensi (Revoke)');

  const targetInput = new TextInputBuilder()
    .setCustomId(ADM_REVOKE_TARGET_INPUT_ID)
    .setLabel('Discord User ID Target')
    .setPlaceholder('Contoh: 111222333444555666')
    .setStyle(TextInputStyle.Short)
    .setMinLength(17)
    .setMaxLength(20)
    .setRequired(true);

  modal.addComponents(new ActionRowBuilder().addComponents(targetInput));

  await interaction.showModal(modal);
}

module.exports = {
  handleRevokeKey,
  ADM_REVOKE_KEY_MODAL_ID,
  ADM_REVOKE_TARGET_INPUT_ID,
};
