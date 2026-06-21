const { SlashCommandBuilder } = require('discord.js');
const musicPlayer = require('../../services/musicPlayer');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');

module.exports = {
  data: new SlashCommandBuilder()
    .setName('resume')
    .setDescription('Melanjutkan (resume) pemutaran musik yang dijeda.'),

  /**
   * @param {import('discord.js').ChatInputCommandInteraction} interaction
   */
  async execute(interaction) {
    await interaction.deferReply({ ephemeral: true });

    const guildId = interaction.guildId;
    const success = musicPlayer.resume(guildId);

    if (success) {
      await interaction.editReply({
        embeds: [successEmbed('Melanjutkan Musik', 'Pemutaran musik berhasil dilanjutkan.')],
      });
      autoDeleteReply(interaction);
    } else {
      await interaction.editReply({
        embeds: [errorEmbed('Tidak ada musik dijeda yang dapat dilanjutkan.')],
      });
      autoDeleteReply(interaction);
    }
  },
};
