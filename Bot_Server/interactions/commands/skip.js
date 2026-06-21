const { SlashCommandBuilder } = require('discord.js');
const musicPlayer = require('../../services/musicPlayer');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

module.exports = {
  data: new SlashCommandBuilder()
    .setName('skip')
    .setDescription('Lewati (skip) lagu yang sedang diputar ke lagu berikutnya.'),

  /**
   * @param {import('discord.js').ChatInputCommandInteraction} interaction
   */
  async execute(interaction) {
    await interaction.deferReply({ ephemeral: true });

    const guildId = interaction.guildId;
    const current = musicPlayer.getCurrentTrack(guildId);

    if (!current) {
      await interaction.editReply({
        embeds: [errorEmbed('Tidak ada musik yang sedang diputar di server ini.')],
      });
      autoDeleteReply(interaction);
      return;
    }

    const res = musicPlayer.skip(guildId);

    if (res.success) {
      if (res.track) {
        await interaction.editReply({
          embeds: [successEmbed('Lagu Dilewati', `Lagu berhasil dilewati. Sekarang memutar:\n**${res.track.title}**`)],
        });
        autoDeleteReply(interaction);
      } else {
        await interaction.editReply({
          embeds: [successEmbed('Playlist Selesai', 'Lagu berhasil dilewati. Playlist telah selesai dan bot keluar dari VC.')],
        });
        autoDeleteReply(interaction);
      }
    } else {
      await interaction.editReply({
        embeds: [errorEmbed('Gagal melewai lagu.')],
      });
      autoDeleteReply(interaction);
    }
  },
};
