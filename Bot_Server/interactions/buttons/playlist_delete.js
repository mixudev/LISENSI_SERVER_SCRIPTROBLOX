const { ActionRowBuilder, StringSelectMenuBuilder } = require('discord.js');
const { getPlaylist } = require('../../services/playlistService');

const PL_SELECT_DELETE_ID = 'pl_select_delete';

/**
 * Handler ketika tombol "Hapus Lagu" ditekan.
 * Menampilkan StringSelectMenu berisi daftar lagu di playlist user.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handlePlaylistDelete(interaction) {
  const userId = interaction.user.id;
  const guildId = interaction.guildId;

  const playlist = getPlaylist(userId, guildId);

  if (playlist.length === 0) {
    await interaction.reply({
      content: 'ℹ️ Playlist Anda kosong, tidak ada lagu yang bisa dihapus.',
      ephemeral: true,
    });
    return;
  }

  // Batasi maks 25 lagu di menu dropdown Discord
  const options = playlist.slice(0, 25).map((track, index) => {
    // Batasi panjang teks agar tidak error (>100 char)
    const label = track.title.length > 90 ? track.title.slice(0, 87) + '...' : track.title;
    return {
      label: label,
      description: `Lagu ke-${index + 1}`,
      value: String(index), // Simpan index sebagai string
      emoji: '🎵',
    };
  });

  const selectMenu = new StringSelectMenuBuilder()
    .setCustomId(PL_SELECT_DELETE_ID)
    .setPlaceholder('Pilih lagu yang ingin dihapus...')
    .addOptions(options);

  const row = new ActionRowBuilder().addComponents(selectMenu);

  await interaction.reply({
    content: '🗑️ Silakan pilih lagu di bawah ini untuk dihapus dari playlist:',
    components: [row],
    ephemeral: true,
  });
}

module.exports = handlePlaylistDelete;
module.exports.PL_SELECT_DELETE_ID = PL_SELECT_DELETE_ID;
