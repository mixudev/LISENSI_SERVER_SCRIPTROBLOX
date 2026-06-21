const { SlashCommandBuilder } = require('discord.js');
const ytdl = require('@distube/ytdl-core');
const { getPlaylist } = require('../../services/playlistService');
const musicPlayer = require('../../services/musicPlayer');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

module.exports = {
  data: new SlashCommandBuilder()
    .setName('play')
    .setDescription('Memutar playlist pribadi Anda atau video YouTube di Voice Channel.')
    .addStringOption((option) =>
      option
        .setName('url')
        .setDescription('URL video/music YouTube yang ingin diputar')
        .setRequired(false)
    )
    .addBooleanOption((option) =>
      option
        .setName('playlist')
        .setDescription('Set ke True untuk memutar seluruh playlist lofi yang Anda simpan')
        .setRequired(false)
    ),

  /**
   * @param {import('discord.js').ChatInputCommandInteraction} interaction
   */
  async execute(interaction) {
    await interaction.deferReply({ ephemeral: true });

    const url = interaction.options.getString('url')?.trim();
    const usePlaylist = interaction.options.getBoolean('playlist');

    // 1. Validasi Voice Channel
    const voiceChannel = interaction.member.voice.channel;
    if (!voiceChannel) {
      await interaction.editReply({
        embeds: [errorEmbed('Anda harus bergabung ke Voice Channel terlebih dahulu untuk memutar musik!')],
      });
      autoDeleteReply(interaction);
      return;
    }

    // 2. Validasi Parameter
    if (!url && !usePlaylist) {
      await interaction.editReply({
        embeds: [errorEmbed('Harap tentukan lagu yang ingin diputar!\nMasukkan link YouTube di opsi `url` ATAU set opsi `playlist` ke `True`.')],
      });
      autoDeleteReply(interaction);
      return;
    }

    let tracks = [];

    // Kasus A: Putar YouTube URL spesifik
    if (url) {
      const isValid = ytdl.validateURL(url);
      if (!isValid) {
        await interaction.editReply({
          embeds: [errorEmbed('Link YouTube tidak valid. Harap periksa kembali URL-nya.')],
        });
        autoDeleteReply(interaction);
        return;
      }

      try {
        const info = await ytdl.getBasicInfo(url);
        const title = info.videoDetails.title || 'YouTube Audio';
        tracks.push({ title, url });
      } catch (err) {
        console.error('❌ Gagal mengambil info YouTube:', err);
        // Fallback jika API fail namun valid URL
        tracks.push({ title: 'YouTube Video', url });
      }
    } 
    // Kasus B: Putar Custom Playlist
    else if (usePlaylist) {
      const playlist = getPlaylist(interaction.user.id, interaction.guildId);
      if (playlist.length === 0) {
        await interaction.editReply({
          embeds: [errorEmbed('Playlist pribadi Anda kosong! Silakan tambahkan lagu terlebih dahulu di panel playlist.')],
        });
        autoDeleteReply(interaction);
        return;
      }
      tracks = playlist;
    }

    // 3. Putar audio di VC
    try {
      const isLoop = usePlaylist ? true : false;
      const res = await musicPlayer.play({
        voiceChannel,
        tracks,
        guildId: interaction.guildId,
        loop: isLoop,
      });

      if (res.success) {
        const infoMsg = url 
          ? `Memutar lagu: **${tracks[0].title}**`
          : `Memutar playlist pribadi Anda (Total ${tracks.length} lagu, Loop aktif).`;

        await interaction.editReply({
          embeds: [successEmbed('Mulai Memutar', `Berhasil bergabung ke VC **${voiceChannel.name}**\n\n${infoMsg}`)],
        });
        autoDeleteReply(interaction);
      } else {
        await interaction.editReply({
          embeds: [errorEmbed(res.message)],
        });
        autoDeleteReply(interaction);
      }
    } catch (err) {
      console.error('❌ [Play Command] Error:', err);
      await interaction.editReply({
        embeds: [errorEmbed('Terjadi kesalahan saat memutar audio. Silakan coba lagi.')],
      });
      autoDeleteReply(interaction);
    }
  },
};
