/**
 * musicPlayer.js — Audio player per-guild menggunakan @discordjs/voice + ytdl-core.
 *
 * Satu instance per guild. Mengelola queue, koneksi VC, dan playback.
 */

const {
  joinVoiceChannel,
  createAudioPlayer,
  createAudioResource,
  AudioPlayerStatus,
  VoiceConnectionStatus,
  entersState,
  getVoiceConnection,
} = require('@discordjs/voice');

const ytdl = require('@distube/ytdl-core');

/** Map<guildId, PlayerState> */
const players = new Map();

/**
 * @typedef {Object} PlayerState
 * @property {import('@discordjs/voice').VoiceConnection} connection
 * @property {import('@discordjs/voice').AudioPlayer} player
 * @property {Array<{title: string, url: string}>} queue
 * @property {number} currentIndex
 * @property {boolean} loop
 * @property {Function|null} onTrackEnd  — callback setelah track selesai
 */

/**
 * Join voice channel dan mulai putar queue.
 * @param {Object} options
 * @param {import('discord.js').VoiceChannel} options.voiceChannel
 * @param {Array<{title: string, url: string}>} options.tracks
 * @param {string} options.guildId
 * @param {boolean} [options.loop=false]
 * @param {Function} [options.onTrackChange] — callback(currentIndex, track)
 * @param {Function} [options.onFinish] — callback saat queue habis
 * @returns {Promise<{ success: boolean, message: string }>}
 */
async function play({ voiceChannel, tracks, guildId, loop = false, onTrackChange, onFinish }) {
  if (!tracks || tracks.length === 0) {
    return { success: false, message: 'Playlist kosong.' };
  }

  // Hentikan player lama jika ada
  await stop(guildId);

  try {
    const connection = joinVoiceChannel({
      channelId:      voiceChannel.id,
      guildId:        guildId,
      adapterCreator: voiceChannel.guild.voiceAdapterCreator,
      selfDeaf:       true,
    });

    // Tunggu sampai koneksi siap
    await entersState(connection, VoiceConnectionStatus.Ready, 10_000);

    const audioPlayer = createAudioPlayer();

    connection.subscribe(audioPlayer);

    const state = {
      connection,
      player: audioPlayer,
      queue: [...tracks],
      currentIndex: 0,
      loop,
      onTrackChange: onTrackChange || null,
      onFinish: onFinish || null,
    };

    players.set(guildId, state);

    // Handle track end
    audioPlayer.on(AudioPlayerStatus.Idle, () => {
      const s = players.get(guildId);
      if (!s) return;

      if (s.loop) {
        s.currentIndex = (s.currentIndex + 1) % s.queue.length;
      } else {
        s.currentIndex++;
      }

      if (s.currentIndex < s.queue.length || s.loop) {
        playTrack(guildId);
      } else {
        // Queue habis
        if (s.onFinish) s.onFinish();
        stop(guildId);
      }
    });

    audioPlayer.on('error', (err) => {
      console.error(`❌ [MusicPlayer] Audio error di guild ${guildId}:`, err.message);
      // Skip ke track berikutnya
      const s = players.get(guildId);
      if (s) {
        s.currentIndex++;
        if (s.currentIndex < s.queue.length) {
          playTrack(guildId);
        } else {
          stop(guildId);
        }
      }
    });

    // Mulai play track pertama
    playTrack(guildId);

    return { success: true, message: `▶️ Memutar **${tracks[0].title}**` };
  } catch (err) {
    console.error(`❌ [MusicPlayer] Error join VC:`, err.message);
    players.delete(guildId);
    return { success: false, message: 'Gagal bergabung ke Voice Channel.' };
  }
}

/**
 * Putar track saat ini dari queue.
 * @param {string} guildId
 */
async function playTrack(guildId) {
  const state = players.get(guildId);
  if (!state) return;

  const track = state.queue[state.currentIndex];
  if (!track) return;

  try {
    if (state.onTrackChange) {
      state.onTrackChange(state.currentIndex, track);
    }

    const stream = ytdl(track.url, {
      filter:  'audioonly',
      quality: 'lowestaudio',
      highWaterMark: 1 << 25,
    });

    const resource = createAudioResource(stream);
    state.player.play(resource);

    console.log(`🎵 [MusicPlayer] Memutar: ${track.title} (guild: ${guildId})`);
  } catch (err) {
    console.error(`❌ [MusicPlayer] Gagal stream ${track.url}:`, err.message);
    state.currentIndex++;
    if (state.currentIndex < state.queue.length) {
      playTrack(guildId);
    } else {
      stop(guildId);
    }
  }
}

/**
 * Pause playback.
 * @param {string} guildId
 */
function pause(guildId) {
  const state = players.get(guildId);
  if (state?.player) {
    state.player.pause();
    return true;
  }
  return false;
}

/**
 * Resume playback.
 * @param {string} guildId
 */
function resume(guildId) {
  const state = players.get(guildId);
  if (state?.player) {
    state.player.unpause();
    return true;
  }
  return false;
}

/**
 * Skip ke lagu berikutnya.
 * @param {string} guildId
 * @returns {{ success: boolean, track: Object|null }}
 */
function skip(guildId) {
  const state = players.get(guildId);
  if (!state) return { success: false, track: null };

  state.currentIndex++;
  if (state.currentIndex >= state.queue.length) {
    stop(guildId);
    return { success: true, track: null };
  }

  playTrack(guildId);
  return { success: true, track: state.queue[state.currentIndex] };
}

/**
 * Stop playback dan disconnect dari VC.
 * @param {string} guildId
 */
async function stop(guildId) {
  const state = players.get(guildId);
  if (state) {
    try { state.player.stop(true); } catch (_) {}
    try { state.connection.destroy(); } catch (_) {}
    players.delete(guildId);
  }

  // Juga destroy via getVoiceConnection jika ada
  try {
    const vc = getVoiceConnection(guildId);
    if (vc) vc.destroy();
  } catch (_) {}
}

/**
 * Cek apakah bot sedang memutar di guild.
 * @param {string} guildId
 * @returns {boolean}
 */
function isPlaying(guildId) {
  const state = players.get(guildId);
  return !!state && state.player.state.status === AudioPlayerStatus.Playing;
}

/**
 * Ambil info track saat ini.
 * @param {string} guildId
 * @returns {{ track: Object|null, index: number, total: number }|null}
 */
function getCurrentTrack(guildId) {
  const state = players.get(guildId);
  if (!state) return null;
  return {
    track: state.queue[state.currentIndex] || null,
    index: state.currentIndex,
    total: state.queue.length,
  };
}

module.exports = { play, pause, resume, skip, stop, isPlaying, getCurrentTrack };
