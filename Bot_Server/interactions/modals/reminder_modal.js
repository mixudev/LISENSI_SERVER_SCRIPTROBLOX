const { EmbedBuilder } = require('discord.js');
const { add } = require('../../services/reminderService');
const { parseTime, formatDate } = require('../../utils/timeParser');
const { errorEmbed, successEmbed, autoDeleteReply } = require('../../utils/replyHelper');
const { REM_INPUT_TIME_ID, REM_INPUT_MESSAGE_ID, REM_INPUT_TAG_ID } = require('../buttons/reminder_add');

/**
 * Handler ketika modal "Tambah Pengingat Baru" disubmit.
 *
 * @param {import('discord.js').ModalSubmitInteraction} interaction
 */
async function handleReminderModalSubmit(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const rawTime    = interaction.fields.getTextInputValue(REM_INPUT_TIME_ID).trim();
  const message    = interaction.fields.getTextInputValue(REM_INPUT_MESSAGE_ID).trim();
  const rawTag     = interaction.fields.getTextInputValue(REM_INPUT_TAG_ID)?.trim() || null;

  // 1. Parse Waktu
  const ms = parseTime(rawTime);
  if (!ms || ms <= 0) {
    await interaction.editReply({
      embeds: [errorEmbed(`Format waktu **"${rawTime}"** tidak valid.\nGunakan format seperti: \`30m\`, \`2h\`, \`1d\`, \`1 hour\`, \`3 days\`.`)]
    });
    autoDeleteReply(interaction);
    return;
  }

  const fireAt = new Date(Date.now() + ms);

  // 2. Format Tag (jika diisi)
  let targetTag = null;
  if (rawTag) {
    if (rawTag.startsWith('<') && rawTag.endsWith('>')) {
      targetTag = rawTag;
    } else if (rawTag.toLowerCase() === '@everyone') {
      targetTag = '@everyone';
    } else if (rawTag.toLowerCase() === '@here') {
      targetTag = '@here';
    } else {
      // Hilangkan karakter non-digit untuk mendeteksi ID
      const cleanId = rawTag.replace(/\D/g, '');
      if (cleanId.length >= 17 && cleanId.length <= 20) {
        // Cek apakah ini role atau member
        const isRole = interaction.guild?.roles.cache.has(cleanId);
        if (isRole) {
          targetTag = `<@&${cleanId}>`;
        } else {
          targetTag = `<@${cleanId}>`;
        }
      } else {
        // Kalau bukan ID, jadikan text biasa
        targetTag = rawTag;
      }
    }
  }

  try {
    // 3. Simpan & Jadwalkan
    const reminder = add({
      userId: interaction.user.id,
      guildId: interaction.guildId,
      message,
      fireAt,
      targetTag,
    });

    const timeStr = formatDate(fireAt);
    const tagMsg = targetTag ? `\n**Target Tag:** ${targetTag}` : '';

    await interaction.editReply({
      embeds: [
        successEmbed(
          'Pengingat Berhasil Diatur!',
          [
            `Saya akan mengingatkan Anda pada:`,
            `**\`${timeStr}\`** (sekitar ${rawTime} lagi)`,
            ``,
            `> **Pesan:** ${message}`,
            tagMsg,
          ].join('\n')
        )
      ]
    });
    autoDeleteReply(interaction);
  } catch (err) {
    console.error('❌ [Reminder Modal] Gagal menambahkan pengingat:', err);
    await interaction.editReply({
      embeds: [errorEmbed('Gagal menyimpan pengingat. Silakan hubungi Admin.')]
    });
    autoDeleteReply(interaction);
  }
}

module.exports = handleReminderModalSubmit;
