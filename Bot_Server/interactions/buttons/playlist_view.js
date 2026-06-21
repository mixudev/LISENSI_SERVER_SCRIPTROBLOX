const { EmbedBuilder } = require('discord.js');
const { getPlaylist } = require('../../services/playlistService');

/**
 * Handler ketika tombol "Lihat Playlist" ditekan.
 * Menampilkan daftar lagu di playlist pribadi user secara ephemeral.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handlePlaylistView(interaction) {
  const userId = interaction.user.id;
  const guildId = interaction.guildId;

  const playlist = getPlaylist(userId, guildId);

  if (playlist.length === 0) {
    await interaction.reply({
      content: 'ℹ️ Playlist Anda saat ini kosong. Gunakan tombol **Tambah Lagu** untuk menambahkan lagu.',
      ephemeral: true,
    });
    return;
  }

  const embed = new EmbedBuilder()
    .setColor(0x9b59b6) // Purple
    .setTitle('📋 Playlist Pribadi Anda')
    .setDescription(
      playlist
        .map((t, index) => {
          return `${index + 1}. 🎵 **${t.title}**\n   🔗 [Tonton di YouTube](${t.url})`;
        })
        .join('\n\n')
    )
    .setFooter({ text: `Total: ${playlist.length} lagu` })
    .setTimestamp();

  await interaction.reply({
    embeds: [embed],
    ephemeral: true,
  });
}

module.exports = handlePlaylistView;
