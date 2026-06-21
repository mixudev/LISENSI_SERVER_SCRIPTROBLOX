const { ActionRowBuilder, StringSelectMenuBuilder } = require('discord.js');
const { getByUser } = require('../../services/reminderService');
const { formatDate } = require('../../utils/timeParser');

const REM_SELECT_DELETE_ID = 'rem_select_delete';

/**
 * Handler ketika tombol "Hapus Pengingat" ditekan.
 * Menampilkan StringSelectMenu berisi list reminder milik user.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handleReminderDelete(interaction) {
  const userId = interaction.user.id;
  const guildId = interaction.guildId;

  const reminders = getByUser(userId, guildId);

  if (reminders.length === 0) {
    await interaction.reply({
      content: 'ℹ️ Anda tidak memiliki pengingat aktif yang bisa dihapus.',
      ephemeral: true,
    });
    return;
  }

  // Buat opsi menu dropdown (maks 25 opsi di Discord)
  const options = reminders.slice(0, 25).map((r) => {
    const timeStr = formatDate(new Date(r.fireAt));
    // Batasi panjang teks agar tidak error (>100 char)
    const label = r.message.length > 90 ? r.message.slice(0, 87) + '...' : r.message;
    return {
      label: label,
      description: `Waktu: ${timeStr}`,
      value: r.id,
      emoji: '⏰',
    };
  });

  const selectMenu = new StringSelectMenuBuilder()
    .setCustomId(REM_SELECT_DELETE_ID)
    .setPlaceholder('Pilih pengingat yang ingin dihapus...')
    .addOptions(options);

  const row = new ActionRowBuilder().addComponents(selectMenu);

  await interaction.reply({
    content: '🗑️ Silakan pilih pengingat di bawah ini untuk dihapus:',
    components: [row],
    ephemeral: true,
  });
}

module.exports = handleReminderDelete;
module.exports.REM_SELECT_DELETE_ID = REM_SELECT_DELETE_ID;
