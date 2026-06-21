const { EmbedBuilder, ActionRowBuilder, ButtonBuilder, ButtonStyle } = require('discord.js');
const musicPlayer = require('./musicPlayer');
const playlistService = require('./playlistService');

/** @type {Map<string, FocusSession>} */
const sessions = new Map();

const DEFAULT_LOFI_URL = 'https://www.youtube.com/watch?v=jfKfPfyJRdk'; // Lofi Girl Live Stream

/**
 * @typedef {Object} FocusSession
 * @property {string} guildId
 * @property {string} creatorId
 * @property {string} voiceChannelId
 * @property {number} focusDuration — dalam menit
 * @property {number} breakDuration — dalam menit
 * @property {'focus'|'break'|'paused'} status
 * @property {'focus'|'break'} previousStatus — status sebelum dipause
 * @property {number} round
 * @property {number} timeLeft — dalam detik
 * @property {NodeJS.Timeout|null} intervalHandle
 * @property {import('discord.js').ChatInputCommandInteraction} interaction — untuk editReply status realtime
 */

/**
 * Memulai sesi fokus .
 * @param {Object} options
 * @param {import('discord.js').ChatInputCommandInteraction} options.interaction
 * @param {import('discord.js').VoiceChannel} options.voiceChannel
 * @param {number} options.focusDuration
 * @param {number} options.breakDuration
 */
async function startSession({ interaction, voiceChannel, focusDuration, breakDuration }) {
  const guildId = interaction.guildId;

  // Hentikan sesi lama jika ada
  if (sessions.has(guildId)) {
    stopSession(guildId);
  }

  const session = {
    guildId,
    creatorId: interaction.user.id,
    voiceChannelId: voiceChannel.id,
    focusDuration,
    breakDuration,
    status: 'focus',
    previousStatus: 'focus',
    round: 1,
    timeLeft: focusDuration * 60,
    intervalHandle: null,
    interaction,
  };

  sessions.set(guildId, session);

  // Jalankan countdown loop
  session.intervalHandle = setInterval(() => tick(guildId), 1000);

  // Update status panel pertama kali
  await updatePanel(guildId);

  console.log(`⏱️ [Focus] Sesi fokus dimulai di guild ${guildId} oleh ${interaction.user.tag}`);
}

/**
 * Detik demi detik countdown.
 * @param {string} guildId
 */
async function tick(guildId) {
  const session = sessions.get(guildId);
  if (!session || session.status === 'paused') return;

  session.timeLeft--;

  // Tiap 5 detik update UI biar ga rate limit Discord API
  if (session.timeLeft % 5 === 0 || session.timeLeft <= 5) {
    await updatePanel(guildId);
  }

  if (session.timeLeft <= 0) {
    if (session.status === 'focus') {
      // Pindah ke Break
      session.status = 'break';
      session.timeLeft = session.breakDuration * 60;

      // Putar lofi music
      await playLofi(session);
      await notifyTransition(session, 'Waktu istirahat tiba! Musik lofi diputar sejenak.');
    } else {
      // Pindah ke Focus
      session.status = 'focus';
      session.timeLeft = session.focusDuration * 60;
      session.round++;

      // Stop musik lofi
      try {
        await musicPlayer.stop(session.guildId);
      } catch (err) {
        console.error('❌ Gagal stop musik lofi saat fokus:', err);
      }

      await notifyTransition(session, `Waktu fokus kembali dimulai! Sesi #${session.round}. Tetap semangat!`);
    }
    await updatePanel(guildId);
  }
}

/**
 * Putar musik lofi di voice channel.
 * Menggunakan lagu dari playlist pembuat jika ada, jika tidak pakai default lofi stream.
 * @param {FocusSession} session
 */
async function playLofi(session) {
  try {
    const client = session.interaction.client;
    const channel = await client.channels.fetch(session.voiceChannelId);
    if (!channel) return;

    // Cari lagu dari playlist pembuat
    const playlist = playlistService.getPlaylist(session.creatorId, session.guildId);
    let tracks = [];

    if (playlist && playlist.length > 0) {
      tracks = playlist;
    } else {
      tracks = [{ title: 'Lofi Girl Stream', url: DEFAULT_LOFI_URL }];
    }

    await musicPlayer.play({
      voiceChannel: channel,
      tracks: tracks,
      guildId: session.guildId,
      loop: true,
    });
  } catch (err) {
    console.error('❌ [Focus] Gagal memutar musik lofi:', err.message);
  }
}

/**
 * Kirim pesan notifikasi transition.
 * @param {FocusSession} session
 * @param {string} text
 */
async function notifyTransition(session, text) {
  try {
    // 1. Kirim DM ke creator
    const creator = await session.interaction.client.users.fetch(session.creatorId);
    if (creator) {
      await creator.send(`**[Focus Timer]** ${text}`).catch(() => null);
    }

    // 2. Kirim mention ke text channel tempat command dijalankan
    await session.interaction.channel.send(`<@${session.creatorId}> ${text}`).catch(() => null);
  } catch (err) {
    console.error('❌ [Focus] Gagal kirim notifikasi transisi:', err.message);
  }
}

/**
 * Pause / Resume timer.
 * @param {string} guildId
 */
function togglePause(guildId) {
  const session = sessions.get(guildId);
  if (!session) return false;

  if (session.status === 'paused') {
    session.status = session.previousStatus;
    if (session.status === 'break') {
      // Resume music player
      musicPlayer.resume(guildId);
    }
  } else {
    session.previousStatus = session.status;
    session.status = 'paused';
    if (session.previousStatus === 'break') {
      // Pause music player
      musicPlayer.pause(guildId);
    }
  }

  updatePanel(guildId);
  return true;
}

/**
 * Skip phase saat ini (Focus -> Break atau sebaliknya).
 * @param {string} guildId
 */
async function skipPhase(guildId) {
  const session = sessions.get(guildId);
  if (!session) return false;

  // Set sisa waktu ke 0 agar trigger transisi di tick berikutnya
  session.timeLeft = 0;
  await tick(guildId);
  return true;
}

/**
 * Hentikan sesi fokus dan bersihkan state.
 * @param {string} guildId
 */
function stopSession(guildId) {
  const session = sessions.get(guildId);
  if (!session) return false;

  if (session.intervalHandle) {
    clearInterval(session.intervalHandle);
  }

  // Stop audio player jika ada
  musicPlayer.stop(guildId).catch(() => null);

  sessions.delete(guildId);
  return true;
}

/**
 * Format detik ke format mm:ss.
 * @param {number} seconds
 * @returns {string}
 */
function formatTime(seconds) {
  const m = Math.floor(seconds / 60).toString().padStart(2, '0');
  const s = (seconds % 60).toString().padStart(2, '0');
  return `${m}:${s}`;
}

/**
 * Update real-time panel status di reply interaksi.
 * @param {string} guildId
 */
async function updatePanel(guildId) {
  const session = sessions.get(guildId);
  if (!session) return;

  const progressPercent = session.status === 'focus'
    ? ((session.focusDuration * 60 - session.timeLeft) / (session.focusDuration * 60)) * 100
    : ((session.breakDuration * 60 - session.timeLeft) / (session.breakDuration * 60)) * 100;

  // Visual progress bar 10 block
  const filledBlocks = Math.round(progressPercent / 10);
  const progressBar = '█'.repeat(filledBlocks) + '░'.repeat(10 - filledBlocks);

  let statusText = '';
  let color = 0x5865f2;

  if (session.status === 'focus') {
    statusText = '**FOKUS** — Matikan gangguan & berkonsentrasilah!';
    color = 0xed4245; // Merah
  } else if (session.status === 'break') {
    statusText = '**ISTIRAHAT** — Regangkan tubuh & dengarkan musik lofi.';
    color = 0x57f287; // Hijau
  } else {
    statusText = '**DIPAUSED** — Waktu diberhentikan sementara.';
    color = 0xfee75c; // Kuning
  }

  const embed = new EmbedBuilder()
    .setColor(color)
    .setTitle(' Focus Timer')
    .setDescription(
      [
        `Sesi aktif untuk: <@${session.creatorId}>`,
        ``,
        `**Status:** ${statusText}`,
        `**Sisa Waktu:** \`${formatTime(session.timeLeft)}\``,
        `**Sesi :** Ke-${session.round}`,
        ``,
        `**Progress:**`,
        `${progressBar} (${Math.round(progressPercent)}%)`,
        ``,
        `*Panel ini ter-update otomatis secara real-time.*`,
      ].join('\n')
    )
    .setFooter({ text: ' Focus Timer • Real-time Status' })
    .setTimestamp();

  const row = new ActionRowBuilder().addComponents(
    new ButtonBuilder()
      .setCustomId('focus_btn_pause')
      .setLabel(session.status === 'paused' ? 'Resume' : 'Pause')
      .setStyle(ButtonStyle.Primary),
    new ButtonBuilder()
      .setCustomId('focus_btn_skip')
      .setLabel('Skip')
      .setStyle(ButtonStyle.Secondary),
    new ButtonBuilder()
      .setCustomId('focus_btn_stop')
      .setLabel('Hentikan')
      .setStyle(ButtonStyle.Danger)
  );

  try {
    await session.interaction.editReply({
      embeds: [embed],
      components: [row],
    });
  } catch (err) {
    // Biasanya error karena interaksi sudah ditutup/dimiss oleh user
    // Kita abaikan saja agar tidak crash
  }
}

/**
 * Dapatkan sesi aktif berdasarkan guildId.
 * @param {string} guildId
 * @returns {FocusSession|undefined}
 */
function getSession(guildId) {
  return sessions.get(guildId);
}

module.exports = { startSession, togglePause, skipPhase, stopSession, getSession };
