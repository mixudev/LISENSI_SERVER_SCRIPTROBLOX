/**
 * PanelManager — Engine multi-channel panel Discord bot.
 *
 * Cara kerja:
 *  1. Daftarkan panel via register()
 *  2. Panggil initAll() saat bot ready → kirim/update embed ke setiap channel
 *  3. Routing interaksi (button/modal) otomatis ke handler panel yang sesuai
 *
 * Cara tambah panel baru:
 *  - Buat file panels/namaPanel.js
 *  - Di bot.js: panelManager.register(require('./panels/namaPanel'))
 *  → Selesai, tidak perlu ubah file lain
 */

const MAX_MESSAGES_IN_CHANNEL = 2;

class PanelManager {
  /**
   * @param {import('discord.js').Client} client
   */
  constructor(client) {
    this.client = client;
    /** @type {Array<Object>} */
    this.panels = [];
  }

  /**
   * Daftarkan satu panel ke manager.
   * @param {Object} panel
   * @param {string}   panel.name            - Nama panel (untuk logging)
   * @param {Function} panel.getChannelId    - Fungsi yang return channel ID
   * @param {Function} panel.getPayload      - Fungsi yang return { embeds, components }
   * @param {Object}   panel.buttonHandlers  - Map customId → handler function
   * @param {Object}   [panel.modalHandlers] - Map customId → handler function (opsional)
   * @returns {PanelManager} this (chainable)
   */
  register(panel) {
    this.panels.push(panel);
    return this;
  }

  /**
   * Inisialisasi semua panel yang terdaftar.
   * Dipanggil saat client ready.
   */
  async initAll() {
    for (const panel of this.panels) {
      await this.ensurePanel(panel);
    }
  }

  /**
   * Pastikan pesan dashboard panel ada & up-to-date di channel-nya.
   * - Ada pesan embed bot → edit (tidak duplikat)
   * - Belum ada → kirim baru
   * - Setelah itu bersihkan pesan lama
   * @param {Object} panel
   */
  async ensurePanel(panel) {
    const channelId = panel.getChannelId();

    if (!channelId) {
      console.warn(`⚠️  Panel "${panel.name}": CHANNEL_ID belum diisi di .env, dilewati.`);
      return;
    }

    try {
      const channel = await this.client.channels.fetch(channelId);

      if (!channel || !channel.isTextBased()) {
        console.error(`❌ Panel "${panel.name}": channel ID "${channelId}" tidak valid atau bukan text channel.`);
        return;
      }

      const recent = await channel.messages.fetch({ limit: 20 });
      const existing = recent.find(
        (msg) => msg.author.id === this.client.user.id && msg.embeds.length > 0
      );

      const payload = panel.getPayload();
      let panelMessageId;

      if (existing) {
        await existing.edit(payload);
        panelMessageId = existing.id;
        console.log(`✅ Panel "${panel.name}" berhasil di-update (edit).`);
      } else {
        const sent = await channel.send(payload);
        panelMessageId = sent.id;
        console.log(`✅ Panel "${panel.name}" berhasil dikirim (baru).`);
      }

      await this.pruneOldMessages(channel, panelMessageId, panel.name);
    } catch (error) {
      console.error(`❌ Gagal inisialisasi panel "${panel.name}":`, error.message);
    }
  }

  /**
   * Hapus pesan lama di channel, sisakan maks MAX_MESSAGES_IN_CHANNEL.
   * @param {import('discord.js').TextChannel} channel
   * @param {string} keepMessageId - ID pesan dashboard yang tidak boleh dihapus
   * @param {string} panelName
   */
  async pruneOldMessages(channel, keepMessageId, panelName) {
    try {
      const messages = await channel.messages.fetch({ limit: 100 });

      const others = messages
        .filter((msg) => msg.id !== keepMessageId)
        .sort((a, b) => a.createdTimestamp - b.createdTimestamp);

      const toDeleteCount = others.size - (MAX_MESSAGES_IN_CHANNEL - 1);
      if (toDeleteCount <= 0) return;

      const toDelete = [...others.values()].slice(0, toDeleteCount);

      for (const msg of toDelete) {
        try {
          await msg.delete();
        } catch (_) {
          // Abaikan jika pesan sudah terhapus atau tidak bisa dihapus (pinned, dll)
        }
      }

      console.log(`🧹 Panel "${panelName}": dihapus ${toDelete.length} pesan lama.`);
    } catch (error) {
      console.error(`❌ Gagal prune pesan panel "${panelName}":`, error.message);
    }
  }

  /**
   * Route interaksi button ke panel yang memiliki handler untuk customId tersebut.
   * @param {import('discord.js').ButtonInteraction} interaction
   * @returns {Promise<boolean>} true jika handler ditemukan
   */
  async routeButton(interaction) {
    for (const panel of this.panels) {
      const handler = panel.buttonHandlers?.[interaction.customId];
      if (handler) {
        await handler(interaction);
        return true;
      }
    }
    console.warn(`⚠️  Tidak ada handler untuk button customId: "${interaction.customId}"`);
    return false;
  }

  /**
   * Route interaksi modal submit ke panel yang memiliki handler untuk customId tersebut.
   * @param {import('discord.js').ModalSubmitInteraction} interaction
   * @returns {Promise<boolean>} true jika handler ditemukan
   */
  async routeModal(interaction) {
    for (const panel of this.panels) {
      const handler = panel.modalHandlers?.[interaction.customId];
      if (handler) {
        await handler(interaction);
        return true;
      }
    }
    console.warn(`⚠️  Tidak ada handler untuk modal customId: "${interaction.customId}"`);
    return false;
  }

  /**
   * Cek apakah suatu channel ID merupakan channel milik salah satu panel.
   * @param {string} channelId
   * @returns {boolean}
   */
  isPanelChannel(channelId) {
    return this.panels.some((panel) => panel.getChannelId() === channelId);
  }

  /**
   * Daftarkan listener messageCreate ke Discord Client.
   * Setiap pesan non-bot yang masuk ke channel panel akan langsung dihapus
   * agar channel tetap bersih dan hanya berisi embed dashboard.
   *
   * CATATAN: Bot harus punya permission "Manage Messages" di channel panel.
   * Panggil ini SETELAH client.once('ready') supaya client.user sudah tersedia.
   */
  watchMessages() {
    this.client.on('messageCreate', async (message) => {
      // Abaikan pesan dari bot sendiri atau pesan DM
      if (message.author.bot || !message.guildId) return;

      // Abaikan jika bukan channel panel
      if (!this.isPanelChannel(message.channelId)) return;

      try {
        await message.delete();
        console.log(`🗑️ Auto-deleted pesan dari ${message.author.tag} di panel channel.`);
      } catch (error) {
        // Biasanya terjadi jika bot tidak punya Manage Messages permission
        console.warn(`⚠️  Gagal hapus pesan di panel channel: ${error.message}`);
      }
    });

    console.log('👁️  PanelManager: messageCreate watcher aktif.');
  }
}

module.exports = PanelManager;
