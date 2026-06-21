const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');
const handleRedeemKey = require('../buttons/redeemKey');

/**
 * Handler submit Modal Redeem Key — Public Panel.
 *
 * @param {import('discord.js').ModalSubmitInteraction} interaction
 */
async function handleRedeemKeyModalSubmit(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const licenseKey = interaction.fields.getTextInputValue(handleRedeemKey.PUB_LICENSE_KEY_INPUT_ID).trim();

  // Validasi format
  if (!/^LZD-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}-[A-F0-9]{6}$/.test(licenseKey)) {
    await interaction.editReply({
      embeds: [errorEmbed('Format License Key tidak valid. Contoh: LZD-XXXXXX-XXXXXX-XXXXXX-XXXXXX')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const discordId = interaction.user.id;
  const displayName = interaction.user.displayName || interaction.user.username;

  const result = await laravelService.redeemKey(licenseKey, discordId, displayName);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal mengikat lisensi ke akun Discord Anda.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  await interaction.editReply({
    embeds: [
      successEmbed(
        '🔑 Lisensi Berhasil Terikat!',
        [
          'License key Anda berhasil dikaitkan ke akun Discord ini.',
          `**License Key:** \`\`\`${licenseKey}\`\`\``,
          '_Sekarang Anda dapat menggunakan menu lainnya seperti Get Stats dan Reset HWID._',
          '_Pesan ini otomatis hilang dalam 20 detik._',
        ].join('\n')
      ),
    ],
  });

  autoDeleteReply(interaction);
}

module.exports = handleRedeemKeyModalSubmit;
