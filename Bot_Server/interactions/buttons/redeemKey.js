const { ModalBuilder, TextInputBuilder, TextInputStyle, ActionRowBuilder } = require('discord.js');

const PUB_REDEEM_KEY_MODAL_ID = 'pub_modal_redeem_key';
const PUB_LICENSE_KEY_INPUT_ID = 'pub_input_license_key';

/**
 * Handler tombol [🔑 Redeem Key] — Public Panel.
 * Membuka modal input license key.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleRedeemKey(interaction) {
  const modal = new ModalBuilder()
    .setCustomId(PUB_REDEEM_KEY_MODAL_ID)
    .setTitle('Redeem License Key');

  const keyInput = new TextInputBuilder()
    .setCustomId(PUB_LICENSE_KEY_INPUT_ID)
    .setLabel('Masukkan License Key Anda')
    .setPlaceholder('Format: LZD-XXXXXX-XXXXXX-XXXXXX-XXXXXX')
    .setStyle(TextInputStyle.Short)
    .setMinLength(31)
    .setMaxLength(31)
    .setRequired(true);

  modal.addComponents(new ActionRowBuilder().addComponents(keyInput));

  await interaction.showModal(modal);
}

module.exports = handleRedeemKey;
handleRedeemKey.PUB_REDEEM_KEY_MODAL_ID = PUB_REDEEM_KEY_MODAL_ID;
handleRedeemKey.PUB_LICENSE_KEY_INPUT_ID = PUB_LICENSE_KEY_INPUT_ID;
