const { ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder } = require('discord.js');
const config = require('../../config');
const { forbiddenEmbed } = require('../../utils/replyHelper');

const ADM_BROADCAST_MODAL_ID = 'adm_modal_broadcast';
const ADM_BROADCAST_MSG_INPUT_ID = 'adm_input_broadcast_msg';

/**
 * Handler tombol [📢 Broadcast] — Admin Panel.
 * Membuka modal input pesan broadcast untuk dikirim ke seluruh user lisensi via DM.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleBroadcastMsg(interaction) {
  const hasAdminRole = interaction.member.roles.cache.has(config.discord.adminRoleId);
  const isAdminUser  = config.discord.adminUserIds.includes(interaction.user.id);

  if (!hasAdminRole && !isAdminUser) {
    await interaction.reply({
      embeds: [forbiddenEmbed('Hanya Staff Admin yang dapat mengirim broadcast.')],
      ephemeral: true,
    });
    return;
  }

  const modal = new ModalBuilder()
    .setCustomId(ADM_BROADCAST_MODAL_ID)
    .setTitle('Kirim Broadcast DM');

  const msgInput = new TextInputBuilder()
    .setCustomId(ADM_BROADCAST_MSG_INPUT_ID)
    .setLabel('Isi Pesan Broadcast')
    .setPlaceholder('Masukkan pengumuman atau info penting untuk dikirim via DM...')
    .setStyle(TextInputStyle.Paragraph)
    .setMinLength(10)
    .setMaxLength(1500)
    .setRequired(true);

  modal.addComponents(new ActionRowBuilder().addComponents(msgInput));

  await interaction.showModal(modal);
}

module.exports = {
  handleBroadcastMsg,
  ADM_BROADCAST_MODAL_ID,
  ADM_BROADCAST_MSG_INPUT_ID,
};
