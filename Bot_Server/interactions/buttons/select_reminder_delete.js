const { remove } = require('../../services/reminderService');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler ketika user memilih opsi di dropdown hapus reminder.
 *
 * @param {import('discord.js').StringSelectMenuInteraction} interaction
 */
async function handleSelectReminderDelete(interaction) {
  await interaction.deferUpdate();

  const reminderId = interaction.values[0];
  const userId     = interaction.user.id;

  const success = remove(reminderId, userId);

  if (success) {
    await interaction.update({
      embeds: [successEmbed('Pengingat Dihapus', 'Pengingat Anda berhasil dihapus dan dibatalkan.')],
      components: [],
    });
    autoDeleteReply(interaction);
  } else {
    await interaction.update({
      embeds: [errorEmbed('Gagal menghapus pengingat. Pengingat mungkin sudah kadaluarsa atau bukan milik Anda.')],
      components: [],
    });
    autoDeleteReply(interaction);
  }
}

module.exports = handleSelectReminderDelete;
