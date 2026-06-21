const { EmbedBuilder } = require('discord.js');
const laravelService = require('../../services/laravelService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');
const { ADM_BROADCAST_MSG_INPUT_ID } = require('../buttons/broadcastMsg');

/**
 * Handler submit modal [Kirim Broadcast DM].
 *
 * @param {import('discord.js').ModalSubmitInteraction} interaction
 */
async function handleBroadcastModalSubmit(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const messageText = interaction.fields.getTextInputValue(ADM_BROADCAST_MSG_INPUT_ID);
  const client = interaction.client;

  // 1. Ambil daftar user aktif
  const result = await laravelService.listUsers();

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message || 'Gagal mengambil daftar user aktif untuk broadcast.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const users = result.data?.data || [];

  if (users.length === 0) {
    await interaction.editReply({
      embeds: [successEmbed('📢 Broadcast Selesai', 'Tidak ada user aktif untuk dikirimi pesan.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  // Dapatkan daftar unik Discord ID (karena satu user bisa punya beberapa key)
  const uniqueDiscordIds = [...new Set(users.map((u) => u.discord_id))];

  await interaction.editReply({
    content: `📢 **Memulai broadcast ke ${uniqueDiscordIds.length} user unik...**`,
  });

  let successCount = 0;
  let failCount = 0;

  for (const discordId of uniqueDiscordIds) {
    try {
      const user = await client.users.fetch(discordId);
      const dmEmbed = new EmbedBuilder()
        .setColor(0x5865f2)
        .setTitle('📢 Pengumuman Penting')
        .setDescription(messageText)
        .setFooter({ text: `Dikirim oleh Admin Server • ${client.user.username}` })
        .setTimestamp();

      await user.send({ embeds: [dmEmbed] });
      successCount++;
    } catch (err) {
      console.warn(`⚠️ Gagal mengirim broadcast ke ${discordId}:`, err.message);
      failCount++;
    }
  }

  await interaction.editReply({
    content: null,
    embeds: [
      successEmbed(
        '📢 Broadcast Selesai',
        [
          'Pesan pengumuman berhasil disebarkan.',
          `Berhasil terkirim: **${successCount}x**`,
          `Gagal (DM ditutup/error): **${failCount}x**`,
          `Total Target: **${uniqueDiscordIds.length}**`,
        ].join('\n')
      ),
    ],
  });

  autoDeleteReply(interaction, 30000);
}

module.exports = handleBroadcastModalSubmit;
