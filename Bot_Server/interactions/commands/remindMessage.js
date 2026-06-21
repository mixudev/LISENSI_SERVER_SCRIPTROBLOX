const { add } = require('../../services/reminderService');
const { parseToMs, formatDate } = require('../../utils/timeParser');
const { errorEmbed, successEmbed } = require('../../utils/replyHelper');

/**
 * Parsing tag mention di awal pesan (jika ada).
 * Mendukung: <@userId>, <@!userId>, <@&roleId>, @everyone, @here
 *
 * @param {string} text
 * @returns {{ targetTag: string|null, remainingText: string }}
 */
function parseMention(text) {
  text = text.trim();

  // Regex untuk mencocokkan mention discord di awal string
  const mentionRegex = /^(<@!?\d+>|<@&\d+>|@everyone|@here)\s*/i;
  const match = mentionRegex.exec(text);

  if (match) {
    return {
      targetTag: match[1],
      remainingText: text.slice(match[0].length).trim(),
    };
  }

  return {
    targetTag: null,
    remainingText: text,
  };
}

/**
 * Mengekstrak durasi dari bagian awal string dan menyisakan pesan.
 *
 * @param {string} text
 * @returns {{ durationStr: string, message: string }}
 */
function extractDurationAndMessage(text) {
  text = text.trim();
  const regex = /^(\d+(?:\.\d+)?)\s*(seconds?|sec|secs|s|minutes?|min|mins|menit|m|hours?|hr|hrs|jam|h|days?|hari|d|weeks?|minggu|w)\s*/i;

  let durationStr = '';
  let currentText = text;
  let match;

  while ((match = regex.exec(currentText)) !== null) {
    durationStr += match[0];
    currentText = currentText.slice(match[0].length);
  }

  return {
    durationStr: durationStr.trim(),
    message: currentText.trim(),
  };
}

/**
 * Handler pesan untuk command prefix !remind.
 * Format: !remind [@mention/role] [duration] [message]
 *
 * @param {import('discord.js').Message} message
 */
async function handleRemindMessageCommand(message) {
  const content = message.content.slice(8).trim(); // Potong "!remind "

  if (!content) {
    const reply = await message.reply({
      embeds: [
        errorEmbed(
          'Gunakan format:\n`!remind [@mention/role] [durasi] [pesan]`\n\nContoh:\n`!remind @user 1 day Event Mobile Legends`\n`!remind 2h Mabar Roblox`'
        ),
      ],
    });
    setTimeout(() => {
      reply.delete().catch(() => {});
      message.delete().catch(() => {});
    }, 5000);
    return;
  }

  // 1. Ekstrak mention
  const { targetTag, remainingText } = parseMention(content);

  // 2. Ekstrak durasi dan pesan
  const { durationStr, message: reminderMsg } = extractDurationAndMessage(remainingText);

  if (!durationStr || !reminderMsg) {
    const reply = await message.reply({
      embeds: [
        errorEmbed(
          'Format tidak valid. Pastikan Anda memasukkan durasi (e.g. `2h`, `1 day`) and pesan pengingat.\n\nContoh:\n`!remind @user 1 day Event Mobile Legends`\n`!remind 2h Mabar Roblox`'
        ),
      ],
    });
    setTimeout(() => {
      reply.delete().catch(() => {});
      message.delete().catch(() => {});
    }, 5000);
    return;
  }

  // 3. Parse durasi ke ms
  const ms = parseToMs(durationStr);
  if (!ms || ms <= 0) {
    const reply = await message.reply({
      embeds: [
        errorEmbed(
          `Durasi **"${durationStr}"** tidak valid. Gunakan format seperti: \`30m\`, \`2h\`, \`1 day\`.`
        ),
      ],
    });
    setTimeout(() => {
      reply.delete().catch(() => {});
      message.delete().catch(() => {});
    }, 5000);
    return;
  }

  const fireAt = new Date(Date.now() + ms);

  try {
    // 4. Tambahkan pengingat
    add({
      userId: message.author.id,
      guildId: message.guildId,
      message: reminderMsg,
      fireAt,
      targetTag,
    });

    const timeStr = formatDate(fireAt);
    const tagMsg = targetTag ? `\n**Target Tag:** ${targetTag}` : '';

    const reply = await message.reply({
      embeds: [
        successEmbed(
          'Pengingat Berhasil Diatur!',
          [
            `Saya akan mengingatkan Anda pada:`,
            `**\`${timeStr}\`** (sekitar ${durationStr} lagi)`,
            ``,
            `> **Pesan:** ${reminderMsg}`,
            tagMsg,
          ].join('\n')
        ),
      ],
    });
    setTimeout(() => {
      reply.delete().catch(() => {});
      message.delete().catch(() => {});
    }, 5000);
  } catch (err) {
    console.error('❌ [!remind] Gagal menambahkan pengingat:', err);
    const reply = await message.reply({
      embeds: [errorEmbed('Gagal menyimpan pengingat. Silakan hubungi Admin.')],
    });
    setTimeout(() => {
      reply.delete().catch(() => {});
      message.delete().catch(() => {});
    }, 5000);
  }
}

module.exports = handleRemindMessageCommand;
