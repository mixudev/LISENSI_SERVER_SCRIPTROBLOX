# 🛠️ Panduan Pemecahan Masalah (Troubleshooting)

Dokumen ini berisi daftar error umum yang sering ditemui pada sistem bot Discord, penyebabnya, dan langkah konkret untuk mengatasinya.

---

## 1. Error: `Used disallowed intents`

### Gejala:
Bot langsung mati (crash) saat dijalankan dengan log error:
`❌ Unhandled promise rejection: Error: Used disallowed intents`

### Penyebab:
Bot dikonfigurasi untuk mendengarkan pesan chat biasa (intent `GatewayIntentBits.MessageContent` di `bot.js`) agar fitur command prefix `!remind` dapat berjalan, tetapi izin tersebut belum diaktifkan di pengaturan bot di Discord Developer Portal.

### Solusi:
1. Buka [Discord Developer Portal](https://discord.com/developers/applications).
2. Pilih aplikasi bot Anda.
3. Klik tab **Bot** di menu sebelah kiri.
4. Gulir ke bawah hingga Anda melihat bagian **Privileged Gateway Intents**.
5. Aktifkan toggle **Message Content Intent** (diaktifkan menjadi warna hijau/biru).
6. Klik tombol **Save Changes** di bawah.
7. Di terminal proyek Anda, restart bot:
   ```bash
   docker compose restart bot
   ```

---

## 2. Error: `Error join VC: The operation was aborted`

### Gejala:
Saat Anda mengetik `/play` atau memulai sesi `/focus`, bot gagal bergabung ke Voice Channel (VC) dan mencetak log error:
`❌ [MusicPlayer] Error join VC: The operation was aborted`

### Penyebab:
1. **Kurang Izin Otorisasi**: Bot Anda diundang (invite) ke server tanpa mencentang izin **Connect** dan **Speak** di generator link invite.
2. **Koneksi Port UDP Terblokir**: Protokol suara Discord menggunakan transmisi data UDP pada port tinggi (biasanya port 50000+). Jika bot dijalankan di dalam kontainer Docker di server VPS yang mengaktifkan firewall ketat, paket UDP tidak dapat diteruskan kembali ke kontainer bot.

### Solusi:
* **Langkah A: Perbarui Izin Bot (Sangat Umum)**
  Gunakan link invite yang diperbarui untuk memberikan izin penuh kepada bot Anda di server Discord (Lihat file panduan: [docs/oauth2-invite.md](file:///d:/WEBSITE/PROJECT/SCRIPT_LISENSI/docs/oauth2-invite.md)).
* **Langkah B: Atur Izin Kategori/Channel VC**
  Pastikan bot memiliki hak akses "View Channel", "Connect", dan "Speak" langsung pada pengaturan izin channel/kategori Voice Channel yang ingin dimasuki.
* **Langkah C: Izinkan Koneksi UDP di Firewall VPS**
  Jika Anda menggunakan VPS (seperti Ubuntu dengan UFW), izinkan trafik UDP keluar/masuk:
  ```bash
  sudo ufw allow proto udp to any port 50000:65535
  ```

---

## 3. Error: `Gagal inisialisasi panel "xxx": Missing Access`

### Gejala:
Bot berjalan tetapi menampilkan error inisialisasi panel, contoh:
`❌ Gagal inisialisasi panel "public": Missing Access`

### Penyebab:
1. ID channel yang Anda masukkan di file `.env` salah atau tidak berada pada server tempat bot diundang.
2. Bot tidak memiliki hak akses untuk **Melihat Channel (View Channel)** atau **Mengirim Pesan (Send Messages)** di channel tersebut.

### Solusi:
1. Pastikan ID channel di `Bot_Server/.env` cocok dengan channel di server Discord Anda (klik kanan channel -> pilih *Copy ID*).
2. Pastikan bot Anda memiliki izin role **View Channel**, **Send Messages**, dan **Read Message History** pada channel tersebut.

---

## 4. Perubahan file `.env` tidak Terbaca di Bot

### Gejala:
Anda sudah mengubah file `.env` Bot_Server (misal mengubah ID Channel), tetapi bot masih memposting ke channel lama atau mengabaikan perubahan.

### Penyebab:
Menjalankan perintah `docker compose restart bot` hanya me-restart proses internal bot di dalam kontainer yang sudah ada. Docker **tidak akan** membaca ulang file `.env` baru ke dalam variabel lingkungan kontainer.

### Solusi:
Buat ulang kontainer bot agar Docker memuat ulang `.env` terbaru:
```bash
docker compose up -d bot
```
