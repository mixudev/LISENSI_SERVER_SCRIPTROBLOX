const { SlashCommandBuilder } = require('discord.js');
const musicPlayer = require('../../services/musicPlayer');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

module.exports = {
  data: new SlashCommandBuilder()
    .setName('pause')
    .setDescription('Menjeda (pause) pemutaran musik saat ini.'),

  /**
   * @param {import('discord.js').ChatInputCommandInteraction} interaction
   */
  async execute(interaction) {
    await interaction.deferReply({ ephemeral: true });

    const guildId = interaction.guildId;
    const success = musicPlayer.pause(guildId);

    if (success) {
      await interaction.editReply({
        embeds: [successEmbed('Musik Dijeda', 'Pemutaran musik berhasil dijeda. Gunakan `/resume` untuk melanjutkan.')],
      });
      autoDeleteReply(interaction);
    } else {
      await interaction.editReply({
        embeds: [errorEmbed('Tidak ada musik aktif yang sedang diputar.')],
      });
      autoDeleteReply(interaction);
    }
  },
};
