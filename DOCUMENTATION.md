# 📖 Pusat Dokumentasi & Panduan Integrasi

Selamat datang di pusat dokumentasi sistem lisensi software dan bot Discord.
Dokumentasi telah dikategorikan ke dalam folder `docs/` untuk mempermudah navigasi pencarian.

Silakan pilih panduan berdasarkan kategori di bawah ini:

---

## 📂 Kategori Dokumentasi

### 1. [🔗 Cara Membuat Link Invite Bot Discord (OAuth2)](file:///d:/WEBSITE/PROJECT/SCRIPT_LISENSI/docs/oauth2-invite.md)
* Berisi daftar **Scopes** (`bot`, `applications.commands`) dan checklist **Bot Permissions** lengkap (termasuk izin Voice VC untuk memutar lagu dan status Pomodoro) serta cara membuat URL invite-nya.

### 2. [🎮 Panduan Integrasi Roblox Account Binding](file:///d:/WEBSITE/PROJECT/SCRIPT_LISENSI/docs/roblox-oauth.md)
* Menjelaskan cara kerja pengaitan akun Roblox ke akun Discord melalui **Roblox OAuth2** (menggunakan domain statis ngrok) serta **Mode Fallback Input Username Manual** (tanpa setup OAuth) untuk mempercepat testing.

### 3. [💳 Panduan Integrasi Midtrans & Pembayaran QRIS](file:///d:/WEBSITE/PROJECT/SCRIPT_LISENSI/docs/midtrans.md)
* Menjelaskan cara kerja pembayaran otomatis tiket pembelian via QRIS Midtrans, penulisan environment `.env`, simulasi tanpa akun Midtrans (Zero-Setup), dan integrasi API Sandbox/Production.

### 4. [🤖 Panduan Perintah & Dashboard Bot Discord](file:///d:/WEBSITE/PROJECT/SCRIPT_LISENSI/docs/bot-commands.md)
* Daftar lengkap perintah bot seperti chat santai AI (`/wolf`), Pomodoro Focus Timer Voice Channel (`/focus`), Smart Reminder (`/remindme` & `!remind`), Playlist Manager (`/play`, `/skip`, `/stop`, dll.), serta cara berinteraksi lewat dashboard UI.

### 5. [🛠️ Panduan Pemecahan Masalah (Troubleshooting)](file:///d:/WEBSITE/PROJECT/SCRIPT_LISENSI/docs/troubleshooting.md)
* Penyelesaian error runtime bot, termasuk:
  * `Used disallowed intents` (Izin Message Content di developer portal).
  * `Error join VC: The operation was aborted` (Solusi error join VC/suara).
  * `Gagal inisialisasi panel: Missing Access` (Izin bot di server/channel).
  * Perubahan `.env` tidak diterapkan di bot.
