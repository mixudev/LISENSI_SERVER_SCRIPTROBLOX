const { EmbedBuilder } = require('discord.js');
const { getByUser } = require('../../services/reminderService');
const { formatDate } = require('../../utils/timeParser');

/**
 * Handler ketika tombol "Daftar Pengingat Saya" ditekan.
 * Menampilkan list reminder aktif milik user secara ephemeral.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleReminderList(interaction) {
  const userId = interaction.user.id;
  const guildId = interaction.guildId;

  const reminders = getByUser(userId, guildId);

  if (reminders.length === 0) {
    await interaction.reply({
      content: 'ℹ️ Anda tidak memiliki pengingat aktif di server ini.',
      ephemeral: true,
    });
    return;
  }

  const embed = new EmbedBuilder()
    .setColor(0x5865f2) // Blurple
    .setTitle('📋 Daftar Pengingat Aktif Anda')
    .setDescription(
      reminders
        .map((r, index) => {
          const timeStr = formatDate(new Date(r.fireAt));
          const tagMsg = r.targetTag ? ` (Tag: ${r.targetTag})` : '';
          return `${index + 1}. ⏰ **${r.message}**\n   📅 \`${timeStr}\`${tagMsg}`;
        })
        .join('\n\n')
    )
    .setFooter({ text: `Total: ${reminders.length} pengingat aktif` })
    .setTimestamp();

  await interaction.reply({
    embeds: [embed],
    ephemeral: true,
  });
}

module.exports = handleReminderList;
