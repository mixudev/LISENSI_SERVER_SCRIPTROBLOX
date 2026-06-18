/**
 * bot.js — Entry point Discord Bot.
 *
 * Arsitektur modular:
 *  - Tambah channel panel baru → buat file di panels/, register satu baris di bawah
 *  - Tambah tombol baru → buat handler di interactions/buttons/, tambah ke panel-nya
 *  - Routing interaksi (button/modal) ditangani otomatis oleh PanelManager
 */

const { Client, GatewayIntentBits, Partials } = require('discord.js');
const config          = require('./config');
const PanelManager    = require('./services/panelManager');
const laravelService  = require('./services/laravelService');
const { errorEmbed }  = require('./utils/replyHelper');

// ─── Import semua panel ────────────────────────────────────────────────────────
const publicPanel = require('./panels/publicPanel'); // Channel user umum
const adminPanel  = require('./panels/adminPanel');  // Channel khusus admin
// Tambah panel baru di sini:
// const extraPanel = require('./panels/extraPanel');

// ─── Discord Client ────────────────────────────────────────────────────────────
const client = new Client({
  intents: [GatewayIntentBits.Guilds, GatewayIntentBits.GuildMessages],
  partials: [Partials.Channel, Partials.Message],
});

// ─── Panel Manager — daftarkan semua panel ────────────────────────────────────
const panelManager = new PanelManager(client);
panelManager
  .register(publicPanel)
  .register(adminPanel);
// Untuk tambah panel baru: .register(extraPanel)

// ─── Event: Bot Ready ─────────────────────────────────────────────────────────
client.once('ready', async () => {
  console.log(`🤖 Bot login sebagai ${client.user.tag}`);
  console.log(`📋 Panel terdaftar: ${panelManager.panels.map((p) => p.name).join(', ')}`);

  // Cek koneksi ke Laravel Backend
  const health = await laravelService.checkHealth();
  if (health.success) {
    console.log('✅ Terhubung ke Laravel Backend:', config.laravel.apiUrl);
  } else {
    console.error('❌ Gagal terhubung ke Laravel Backend:', health.message);
    console.error('   Periksa LARAVEL_API_URL dan LARAVEL_API_TOKEN di .env Bot_Server');
  }

  // Kirim / update semua panel ke channel masing-masing
  await panelManager.initAll();

  // Aktifkan auto-delete pesan user di panel channels (real-time)
  panelManager.watchMessages();
});

// ─── Event: Interaksi (Button & Modal) ────────────────────────────────────────
client.on('interactionCreate', async (interaction) => {
  try {
    if (interaction.isButton()) {
      await panelManager.routeButton(interaction);
      return;
    }

    if (interaction.isModalSubmit()) {
      await panelManager.routeModal(interaction);
      return;
    }
  } catch (error) {
    console.error(`❌ Error saat menangani interaksi "${interaction.customId}":`, error);

    const errorPayload = {
      embeds: [errorEmbed('Terjadi kesalahan tak terduga. Silakan coba lagi atau hubungi Admin.')],
      ephemeral: true,
    };

    try {
      if (interaction.deferred || interaction.replied) {
        await interaction.editReply(errorPayload);
      } else {
        await interaction.reply(errorPayload);
      }
    } catch (replyError) {
      console.error('❌ Gagal mengirim pesan error ke user:', replyError);
    }
  }
});

// ─── Error Handling ───────────────────────────────────────────────────────────
client.on('error', (error) => console.error('❌ Discord client error:', error));
process.on('unhandledRejection', (error) => console.error('❌ Unhandled promise rejection:', error));

// ─── Login ────────────────────────────────────────────────────────────────────
client.login(config.discord.token);
