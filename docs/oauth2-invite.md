# 🔗 Panduan Generate Discord Bot Invite Link (OAuth2) & Configuration

Dokumen ini menjelaskan langkah-langkah untuk membuat link invite bot Discord dengan hak akses (*Scopes & Permissions*) yang tepat serta konfigurasi *Gateway Intents* agar semua fitur bot (dashboard lisensi, integrasi Roblox via web, sistem tiket dinamis, panel admin super, dan fitur voice channel lofi) dapat berjalan 100% tanpa error.

Jika Anda mengalami error seperti `Missing Access`, `Error join VC`, atau kegagalan saat menjalankan fungsi *Broadcast* dan *List Users*, hal tersebut biasanya disebabkan oleh kurangnya izin (*permissions*) atau belum aktifnya *Intents* pada Discord Developer Portal.

---

## 1. Langkah Pembuatan Link Invite (OAuth2)

1. Buka [Discord Developer Portal](https://discord.com/developers/applications).
2. Pilih aplikasi bot Anda (e.g., **WOLF**).
3. Klik menu **OAuth2** di bilah navigasi sebelah kiri, lalu pilih sub-menu **URL Generator**.
4. Di bagian **Scopes**, centang kotak berikut:
   - [x] `bot` *(Mengaktifkan fungsi dasar bot)*
   - [x] `applications.commands` *(Sangat Penting! Mengizinkan registrasi tombol, modal/pop-up, serta penggunaan Slash Commands seperti `/focus`, `/play`, `/remindme`, dll.)*

5. Setelah Anda mencentang `bot`, bagian **Bot Permissions** akan muncul di bagian bawah halaman. Silakan centang izin-izin spesifik pada checklist di bawah ini.

---

## 2. Checklist Bot Permissions

Harap centang izin berikut agar seluruh fitur otomatisasi dan manajemen berjalan maksimal:

### A. General Permissions (Izin Umum)
- [x] **View Channels** *(Sangat Penting! Agar bot bisa melihat channel dashboard, tiket, dan admin)*
- [x] **Manage Channels** *(Penting! Dibutuhkan untuk membuat channel privat baru saat user klik 'Open Ticket', mengubah namanya saat diproses, dan menghapusnya otomatis saat tiket ditutup)*
- [x] **Manage Roles** *(Penting! Dibutuhkan untuk sinkronisasi admin serta mengunci/mengatur hak akses channel privat tiket agar hanya bisa dilihat oleh pembuat tiket & Staff)*

### B. Text Permissions (Izin Pesan Teks)
- [x] **Send Messages** *(Mengirim respon, membuat pesan abadi, & update panel)*
- [x] **Send Messages in Threads** *(Mengizinkan bot merespon jika sistem tiket atau fitur teks menggunakan thread channel)*
- [x] **Embed Links** *(Sangat Penting! Semua tampilan dashboard, statistik lisensi, panel tiket, dan panel admin menggunakan format Embed yang rapi dan estetik)*
- [x] **Attach Files** *(Penting! Digunakan untuk mengirim invoice QRIS Midtrans atau mengizinkan user mengirim screenshot bukti transfer/error di room tiket)*
- [x] **Read Message History** *(Sangat Penting! PanelManager butuh ini untuk membaca riwayat pesan abadi saat bot restart agar tidak terjadi dobel kirim pesan tombol)*
- [x] **Manage Messages** *(Penting! Untuk menghapus otomatis pesan chat tersesat dari user di dalam channel panel agar tetap bersih)*
- [x] **Mention Everyone** *(Penting! Memperbolehkan bot melakukan tag `@Admin` atau `@Staff` secara otomatis di dalam room privat saat ada tiket baru masuk)*
- [x] **Use Slash Commands** *(Penting! Fitur Button dan Modal/Pop-up terhitung sebagai bagian dari sistem aplikasi interaksi ini)*

### C. Voice Permissions (Izin Suara - Pomodoro & Play Lofi)
- [x] **Connect** *(Mengizinkan bot masuk ke Voice Channel)*
- [x] **Speak** *(Mengizinkan bot memutar musik lofi/suara notifikasi transisi)*
- [x] **Use Voice Activity** *(Diperlukan oleh sistem Discord API suara)*

---

## ⚠️ 3. Langkah Wajib: Mengaktifkan Privileged Gateway Intents

Karena bot ini memiliki fitur **👥 List Users** (mengambil seluruh data pemegang lisensi dari server) dan **📢 Broadcast** (mengirim pesan pengumuman massal secara privat ke DM seluruh member aktif), Anda **WAJIB** menyalakan fitur ini di portal pengembang. Jika tidak, bot akan *crash* atau data member akan kembali kosong (*empty/undefined*).

1. Pada menu sebelah kiri Discord Developer Portal, klik tab **Bot**.
2. Scroll ke bawah sampai Anda menemukan bagian **Privileged Gateway Intents**.
3. Geser sakelar menjadi **ON (Hijau)** untuk ketiga opsi berikut:
   - [x] **Presence Intent** ➔ **ON**
   - [x] **Server Members Intent** ➔ **ON** *(Sangat krusial untuk berjalannya fitur List Users dan fungsi Broadcast DM)*
   - [x] **Message Content Intent** ➔ **ON** *(Dibutuhkan untuk mendeteksi teks di dalam room tiket)*
4. Klik **Save Changes** di bagian bawah.

---

## 4. Cara Mengundang & Memperbarui Bot

1. Setelah menyelesaikan pengaturan *Permissions* di **URL Generator**, scroll ke paling bawah halaman tersebut.
2. Anda akan melihat link yang dihasilkan pada kolom **GENERATED URL** (contoh: `https://discord.com/oauth2/authorize?client_id=...&permissions=...&scope=bot+applications.commands`).
3. Klik **Copy** pada link tersebut.
4. Buka tab baru di browser Anda, paste link tersebut, pilih server Discord utama Anda, dan klik **Authorize**.
5. **Otorisasi Ulang (Re-invite):** Jika bot sudah berada di dalam server Anda sebelumnya, Anda tidak perlu mengeluarkan (*kick*) bot tersebut. Cukup jalankan langkah di atas (buka link invite baru di browser) dan klik **Authorize**. Ini akan langsung memperbarui berkas izin (*update permissions*) bot Anda secara instan di latar belakang server tanpa mengganggu sistem yang sedang berjalan.