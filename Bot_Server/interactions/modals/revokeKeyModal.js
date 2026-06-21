const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');
const { ADM_REVOKE_TARGET_INPUT_ID } = require('../buttons/revokeKey');

/**
 * Handler submit modal [Cabut Lisensi (Revoke)].
 *
 * @param {import('discord.js').ModalSubmitInteraction} interaction
 */
async function handleRevokeKeyModalSubmit(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const targetDiscordId = interaction.fields.getTextInputValue(ADM_REVOKE_TARGET_INPUT_ID).trim();
  const actorDiscordId = interaction.user.id;

  const result = await laravelService.revokeKey(targetDiscordId, actorDiscordId);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal mencabut lisensi.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const data = result.data?.data;

  await interaction.editReply({
    embeds: [
      successEmbed(
        '🚫 Lisensi Dicabut',
        [
          `Lisensi milik user <@${targetDiscordId}> (${targetDiscordId}) berhasil dicabut.`,
          `Key yang dicabut: \`${data?.key || '—'}\``,
          `Status sekarang: **${data?.status || 'suspended'}**`,
          '',
          '_Semua aktivitas pencabutan lisensi dicatat di server._',
          '',
          '_Pesan ini otomatis hilang dalam 20 detik._',
        ].join('\n')
      ),
    ],
  });

  autoDeleteReply(interaction);
}

module.exports = handleRevokeKeyModalSubmit;
