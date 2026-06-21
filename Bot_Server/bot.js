/**
 * bot.js — Entry point Discord Bot.
 *
 * Arsitektur modular:
 *  - Tambah channel panel baru → buat file di panels/, register satu baris di bawah
 *  - Tambah tombol baru → buat handler di interactions/buttons/, tambah ke panel-nya
 *  - Routing interaksi (button/modal) ditangani otomatis oleh PanelManager
 */

const { Client, GatewayIntentBits, Partials, REST, Routes } = require('discord.js');
const config          = require('./config');
const PanelManager    = require('./services/panelManager');
const laravelService  = require('./services/laravelService');
const { errorEmbed }  = require('./utils/replyHelper');
const wolfCommand     = require('./interactions/commands/wolf');

// ─── Import semua panel ────────────────────────────────────────────────────────
const publicPanel = require('./panels/publicPanel'); // Channel user umum
const adminPanel  = require('./panels/adminPanel');  // Channel khusus admin
const robloxPanel = require('./panels/robloxPanel'); // Channel kaitan Roblox
const ticketPanel = require('./panels/ticketPanel'); // Channel sistem tiket

// ─── Discord Client ────────────────────────────────────────────────────────────
const client = new Client({
  intents: [GatewayIntentBits.Guilds, GatewayIntentBits.GuildMessages],
  partials: [Partials.Channel, Partials.Message],
});

// ─── Panel Manager — daftarkan semua panel ────────────────────────────────────
const panelManager = new PanelManager(client);
panelManager
  .register(publicPanel)
  .register(adminPanel)
  .register(robloxPanel)
  .register(ticketPanel);

// Fungsi sinkronisasi daftar Discord Admin dari Laravel Database
async function syncDiscordAdmins() {
  try {
    const res = await laravelService.getDiscordAdmins();
    if (res.success && Array.isArray(res.data?.data)) {
      const dbAdmins = res.data.data;
      const envAdmins = (process.env.ADMIN_USER_IDS || '')
        .split(',')
        .map((id) => id.trim())
        .filter(Boolean);
      
      const combined = Array.from(new Set([...envAdmins, ...dbAdmins]));
      config.discord.adminUserIds = combined;
      console.log(`👥 [Admins Sync] Berhasil sinkronisasi ${combined.length} admin (${dbAdmins.length} dari DB, ${envAdmins.length} dari .env)`);
    }
  } catch (err) {
    console.error('❌ [Admins Sync] Gagal sinkronisasi admin Discord:', err);
  }
}

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

  // Sinkronisasi Admin awal dan atur interval berkala (60 detik)
  await syncDiscordAdmins();
  setInterval(syncDiscordAdmins, 60000);

  // Daftarkan slash command /wolf ke Discord (Guild level & Global backup)
  const rest = new REST({ version: '10' }).setToken(config.discord.token);
  const commandData = [wolfCommand.data.toJSON()];
  try {
    console.log('⏳ Mendaftarkan slash commands...');
    // 1. Guild level (instan untuk server tempat bot bergabung)
    const guilds = await client.guilds.fetch();
    for (const [guildId, guild] of guilds) {
      await rest.put(
        Routes.applicationGuildCommands(client.user.id, guildId),
        { body: commandData }
      );
      console.log(`   ✅ Command terdaftar instan di guild: ${guild.name || guildId}`);
    }
    // 2. Global level (backup agar command tetap jalan di guild baru)
    await rest.put(
      Routes.applicationCommands(client.user.id),
      { body: commandData }
    );
    console.log('✅ Registrasi slash commands selesai!');
  } catch (cmdErr) {
    console.error('❌ Gagal mendaftarkan slash commands:', cmdErr);
  }

  // Kirim / update semua panel ke channel masing-masing
  await panelManager.initAll();

  // Aktifkan auto-delete pesan user di panel channels (real-time)
  panelManager.watchMessages();
});

// ─── Event: Interaksi (Button, Modal, & Slash Command) ────────────────────────
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

    if (interaction.isChatInputCommand()) {
      if (interaction.commandName === 'wolf') {
        await wolfCommand.execute(interaction);
      }
      return;
    }
  } catch (error) {
    console.error(`❌ Error saat menangani interaksi "${interaction.customId || interaction.commandName}":`, error);

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
