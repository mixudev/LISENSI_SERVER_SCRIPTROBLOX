const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');
const handleLinkRoblox = require('../buttons/linkRoblox');

/**
 * Handler submit Modal Link Roblox — Roblox Panel.
 *
 * @param {import('discord.js').ModalSubmitInteraction} interaction
 */
async function handleLinkRobloxModalSubmit(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const robloxUsername = interaction.fields.getTextInputValue(handleLinkRoblox.PUB_ROBLOX_USERNAME_INPUT_ID).trim();

  if (robloxUsername.length < 3 || robloxUsername.length > 64) {
    await interaction.editReply({
      embeds: [errorEmbed('Username Roblox harus berukuran 3–64 karakter.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const discordId = interaction.user.id;
  const result = await laravelService.linkRoblox(discordId, robloxUsername);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal mengaitkan akun Roblox Anda.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  await interaction.editReply({
    embeds: [
      successEmbed(
        '🎮 Akun Roblox Terikat!',
        [
          `Akun Roblox **${robloxUsername}** berhasil dikaitkan ke Discord Anda.`,
          '_Mulai sekarang, lisensi Anda dikunci hanya untuk akun Roblox ini._',
          '_Pesan ini otomatis hilang dalam 20 detik._',
        ].join('\n')
      ),
    ],
  });

  autoDeleteReply(interaction);
}

module.exports = handleLinkRobloxModalSubmit;
