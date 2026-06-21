const { SlashCommandBuilder } = require('discord.js');
const musicPlayer = require('../../services/musicPlayer');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

module.exports = {
  data: new SlashCommandBuilder()
    .setName('stop')
    .setDescription('Hentikan pemutaran musik dan keluarkan bot dari Voice Channel.'),

  /**
   * @param {import('discord.js').ChatInputCommandInteraction} interaction
   */
  async execute(interaction) {
    await interaction.deferReply({ ephemeral: true });

    const guildId = interaction.guildId;
    const current = musicPlayer.getCurrentTrack(guildId);

    if (!current) {
      // Disconnect saja sebagai fallback jika bot nyangkut di VC tapi ga main lagu
      try {
        await musicPlayer.stop(guildId);
      } catch (_) {}
      
      await interaction.editReply({
        embeds: [successEmbed('Selesai', 'Bot berhasil keluar dari Voice Channel.')],
      });
      autoDeleteReply(interaction);
      return;
    }

    try {
      await musicPlayer.stop(guildId);
      await interaction.editReply({
        embeds: [successEmbed('Musik Dihentikan', 'Pemutaran musik telah dihentikan dan bot keluar dari Voice Channel.')],
      });
      autoDeleteReply(interaction);
    } catch (err) {
      console.error('❌ Gagal stop music player:', err);
      await interaction.editReply({
        embeds: [errorEmbed('Terjadi kesalahan saat menghentikan musik.')],
      });
      autoDeleteReply(interaction);
    }
  },
};
