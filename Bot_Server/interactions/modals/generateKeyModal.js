const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');
const {
  ADM_TARGET_DISCORD_ID_INPUT,
  ADM_DURATION_INPUT_ID,
} = require('../buttons/generateKey');

/**
 * Handler submit Modal Generate Key — Admin Panel.
 * Satu Discord User ID hanya boleh punya satu license key.
 *
 * @param {import('discord.js').ModalSubmitInteraction} interaction
 */
async function handleGenerateKeyModalSubmit(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const targetDiscordId = interaction.fields.getTextInputValue(ADM_TARGET_DISCORD_ID_INPUT).trim();
  const rawDuration     = interaction.fields.getTextInputValue(ADM_DURATION_INPUT_ID).trim();
  const durationDays    = Number(rawDuration);

  if (!/^\d{17,20}$/.test(targetDiscordId)) {
    await interaction.editReply({
      embeds: [errorEmbed('Discord User ID tidak valid. Harus 17–20 digit angka.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  if (!Number.isInteger(durationDays) || durationDays < 0) {
    await interaction.editReply({
      embeds: [errorEmbed('Durasi hari harus berupa angka bulat ≥ 0. Isi 0 untuk Lifetime.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const actorDiscordId = interaction.user.id;
  const result = await laravelService.generateKey(targetDiscordId, actorDiscordId, durationDays);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message)],
    });
    autoDeleteReply(interaction);
    return;
  }

  const payload  = result.data?.data || {};
  const newKey   = payload.key || 'N/A';
  const expHuman = payload.expires_at_human || 'Lifetime';

  await interaction.editReply({
    embeds: [
      successEmbed(
        '🔑 Key Baru Berhasil Dibuat',
        [
          `**Target Discord ID:** \`${targetDiscordId}\``,
          `**License Key:** \`\`\`${newKey}\`\`\``,
          `**Durasi:** ${durationDays === 0 ? 'Lifetime' : `${durationDays} hari`}`,
          `**Expired:** ${expHuman}`,
          '',
          '_Key ini hanya ditampilkan sekali — simpan segera!_',
          '_Pesan ini otomatis hilang dalam 60 detik._',
        ].join('\n')
      ),
    ],
  });

  // Admin butuh waktu lebih untuk catat key → 60 detik
  autoDeleteReply(interaction, 60_000);
}

module.exports = handleGenerateKeyModalSubmit;
