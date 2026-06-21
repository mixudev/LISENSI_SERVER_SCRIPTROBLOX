const laravelService = require('../../services/laravelService');
const { errorEmbed, autoDeleteReply } = require('../../utils/replyHelper');

async function handleGetScript(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const discordId = interaction.user.id;
  const result = await laravelService.getScriptTemplate(discordId);

  if (!result.success) {
    await interaction.editReply({
      embeds: [errorEmbed(result.message)],
    });

    autoDeleteReply(interaction);
    return;
  }

  const payload = result.data?.data;
  const script = payload?.script || '';

  await interaction.editReply({
    content:
`**Here is your script:**

\`\`\`lua
${script}
\`\`\`

*Pesan ini otomatis hilang dalam 30 detik.*`,
  });

  autoDeleteReply(interaction, 30_000);
}

module.exports = handleGetScript;