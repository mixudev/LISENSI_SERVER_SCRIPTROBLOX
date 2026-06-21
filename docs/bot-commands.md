# 🤖 Dokumentasi Perintah & Dashboard Bot Discord

Dokumen ini menjelaskan seluruh daftar perintah (Slash Commands) dan interaksi tombol (Dashboard Control Panels) yang dimiliki oleh bot.

---

## 1. Perintah Slash (Slash Commands)

### A. Obrolan Santai AI (`/wolf [prompt]`)
* **Fungsi:** Mengajak ngobrol bot AI (Wolf).
* **Karakter Respon:** Bahasa santai, gaul Indonesia sehari-hari, jawaban singkat dan padat (seperti teman chatting biasa).
* **API Key Fallback:** Mendukung Gemini, Groq, OpenRouter yang diatur via Admin Dashboard Laravel.

### B. Pomodoro Focus Timer (`/focus`)
* **Syarat:** User wajib bergabung di Voice Channel terlebih dahulu.
* **Fungsi:** Memulai sesi fokus Pomodoro. Memunculkan Modal UI untuk mengatur:
  - *Waktu Fokus (Menit)* — Default: 25.
  - *Waktu Istirahat (Menit)* — Default: 5.
* **Real-time Status Panel:** Menampilkan status Pomodoro secara *ephemeral* (hanya pembuat sesi yang bisa melihat) dengan info progress bar dan tombol:
  - ⏸️ / ▶️ **Pause / Resume** — Menjeda/melanjutkan timer.
  - ⏭️ **Skip** — Melewati fase fokus/istirahat saat ini.
  - ⏹️ **Hentikan** — Menyelesaikan sesi Pomodoro dan memutuskan bot dari VC.
* **Lofi Music:** Saat waktu istirahat tiba, bot otomatis masuk ke VC pembuat sesi dan memutar musik lofi (menggunakan playlist lagu user, atau default Lofi Girl Live Stream).

### C. Smart Reminder (`/remindme [durasi] [pesan] [tag]`)
* **Fungsi:** Mengatur pengingat.
* **Parameter:**
  - `durasi`: Contoh: `30m`, `2h`, `1 day`.
  - `pesan`: Pesan pengingat (contoh: `Mabar Roblox`).
  - `tag` (Opsional): Role/User Discord yang ingin di-tag ketika pengingat berbunyi.

### D. Putar Audio (`/play [url] [playlist]`)
* **Fungsi:** Memutar audio di Voice Channel Anda.
* **Parameter:**
  - `url` (Opsional): Link video/music YouTube spesifik yang ingin diputar.
  - `playlist` (Opsional): Set ke `True` jika ingin memutar seluruh playlist lagu yang Anda simpan.
* **`/skip`**: Melewati lagu yang sedang diputar ke lagu berikutnya.
* **`/pause`**: Menjeda lagu.
* **`/resume`**: Melanjutkan lagu yang dijeda.
* **`/stop`**: Menghentikan musik dan mengeluarkan bot dari VC.

---

## 2. Perintah Prefix Pesan (Message Commands)

### A. `!remind [@user/role] [durasi] [pesan]`
* **Fungsi:** Mengatur pengingat via pesan teks biasa (dapat dipanggil di channel mana saja).
* **Contoh Penggunaan:**
  - `!remind @roblox_role 1 day Turnamen Roblox`
  - `!remind 2h Istirahat dulu` (Tanpa tag, hanya men-tag pembuatnya).

---

## 3. Panel Dashboard Statis (Dashboard Channels)

### A. Panel Pengingat (`panels/reminderPanel.js`)
* Dikirimkan di channel `REMINDER_CHANNEL_ID`.
* **Tombol Interaktif:**
  - ⏰ **Tambah Pengingat** — Membuka Modal UI input pengingat baru.
  - 📋 **Daftar Pengingat Saya** — Melihat daftar pengingat aktif Anda (*ephemeral*).
  - 🗑️ **Hapus Pengingat** — Memilih pengingat aktif Anda untuk dihapus via dropdown *Select Menu*.

### B. Panel Playlist (`panels/playlistPanel.js`)
* Dikirimkan di channel `PLAYLIST_CHANNEL_ID`.
* **Tombol Interaktif:**
  - 🎵 **Tambah Lagu** — Menyimpan video YouTube baru ke playlist Anda (maks 50 lagu).
  - 📋 **Lihat Playlist** — Menampilkan daftar lagu di playlist pribadi Anda (*ephemeral*).
  - ▶️ **Putar Playlist** — Memutar loop playlist lagu Anda di Voice Channel.
  - 🗑️ **Hapus Lagu** — Memilih lagu dari playlist untuk dihapus via dropdown *Select Menu*.
