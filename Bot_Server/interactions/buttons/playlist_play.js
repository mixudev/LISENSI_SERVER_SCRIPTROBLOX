const { getPlaylist } = require('../../services/playlistService');
const musicPlayer = require('../../services/musicPlayer');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

/**
 * Handler ketika tombol "Putar Playlist" ditekan.
 * Memutar semua lagu di playlist pribadi user ke Voice Channel.
 *
 * @param {import('discord.js').ButtonInteraction} interaction
 */
async function handlePlaylistPlay(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const voiceChannel = interaction.member.voice.channel;
  if (!voiceChannel) {
    await interaction.editReply({
      embeds: [errorEmbed('Anda harus berada di Voice Channel terlebih dahulu untuk memutar playlist!')],
    });
    autoDeleteReply(interaction);
    return;
  }

  const playlist = getPlaylist(interaction.user.id, interaction.guildId);
  if (playlist.length === 0) {
    await interaction.editReply({
      embeds: [errorEmbed('Playlist Anda kosong! Tambahkan lagu terlebih dahulu sebelum memutarnya.')],
    });
    autoDeleteReply(interaction);
    return;
  }

  try {
    const res = await musicPlayer.play({
      voiceChannel,
      tracks: playlist,
      guildId: interaction.guildId,
      loop: true,
    });

    if (res.success) {
      await interaction.editReply({
        embeds: [successEmbed('Playlist Diputar', `Berhasil bergabung ke **${voiceChannel.name}** dan memutar playlist Anda (Total ${playlist.length} lagu, Loop aktif).`)],
      });
    } else {
      await interaction.editReply({
        embeds: [errorEmbed(res.message)],
      });
    }
    autoDeleteReply(interaction);
  } catch (err) {
    console.error('❌ [Playlist Play] Gagal memutar playlist:', err);
    await interaction.editReply({
      embeds: [errorEmbed('Gagal memutar playlist. Silakan coba lagi.')],
    });
    autoDeleteReply(interaction);
  }
}

module.exports = handlePlaylistPlay;
