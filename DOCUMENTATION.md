# 📖 Panduan Pengembangan & Integrasi Sistem Lisensi

Dokumen ini menjelaskan solusi untuk tantangan *redirect URI* Roblox OAuth saat menggunakan ngrok, alur kerja sistem tiket baru, integrasi QRIS Midtrans, serta penyelesaian masalah koneksi bot ke backend.

---

## 1. Solusi Redirect URI Roblox OAuth & ngrok

Saat menggunakan ngrok gratis, URL domain Anda berubah setiap kali ngrok dijalankan ulang (misalnya `https://996a-180-247-240-85.ngrok-free.app`). Karena Roblox OAuth memerlukan registrasi *Redirect URI* yang presisi di Roblox Creator Dashboard, perubahan URL ini dapat menghambat produktivitas pengembangan.

Berikut adalah dua solusi yang kami terapkan pada sistem Anda:

### Solusi A: Menggunakan Domain Statis ngrok (Sangat Direkomendasikan)
ngrok sekarang menyediakan **1 domain statis gratis** untuk setiap akun. Dengan ini, URL Anda tidak akan pernah berubah.

1. Buka [ngrok Dashboard](https://dashboard.ngrok.com/) dan klaim domain statis gratis Anda (misalnya `nama-anda.ngrok-free.app`).
2. Update file `Backend/.env` pada bagian `APP_URL`:
   ```env
   APP_URL=https://nama-anda.ngrok-free.app
   ```
3. Jalankan ngrok di komputer Anda dengan menentukan domain tersebut:
   ```bash
   ngrok http --domain=nama-anda.ngrok-free.app 8000
   ```
4. Daftarkan URL callback permanen berikut pada Roblox OAuth Application Anda di Roblox Creator Dashboard:
   ```
   https://nama-anda.ngrok-free.app/roblox/callback
   ```

---

### Solusi B: Menggunakan Fitur Manual Input Fallback (Tanpa Setup OAuth)
Jika Anda ingin melakukan pengujian cepat fitur pengaitan akun Roblox tanpa perlu membuat aplikasi Roblox OAuth atau memperbarui *Redirect URI*:

1. Kosongkan `ROBLOX_CLIENT_ID` di file `Backend/.env`:
   ```env
   ROBLOX_CLIENT_ID=
   ROBLOX_CLIENT_SECRET=
   ```
2. Ketika user mengklik **Kaitkan Akun**, sistem akan secara otomatis mendeteksi bahwa OAuth tidak aktif dan menampilkan halaman **Manual Input Username**.
3. Sistem akan memverifikasi keberadaan username tersebut langsung ke API publik Roblox (`users.roblox.com`) dan menghubungkannya dengan akun Discord user tanpa memerlukan alur OAuth. Ini sangat mempermudah fase *development*.

---

## 2. Fitur Tiket & Pembayaran QRIS Midtrans

Sistem tiket telah ditingkatkan agar mendukung pemilihan kategori tiket dan pembuatan invoice QRIS otomatis.

### A. Alur Pembuatan Tiket
1. User mengklik tombol **Open Ticket** di Discord.
2. Bot merespon secara *ephemeral* (hanya bisa dilihat oleh user bersangkutan) dengan 2 tombol pilihan kategori:
   * 🐞 **Tanya Teknis / Bug Report**
   * 💳 **Pembelian Lisensi**

### B. Tiket Tanya Teknis / Bug Report (`tkt_type_support`)
* Bot membuat channel privat `ticket-XXXXX` (di mana `XXXXX` adalah 5 digit angka acak untuk menghindari duplikasi).
* Menyediakan tombol:
  * 🔄 **Proses Ticket** (Khusus Admin untuk mengubah status ke "Sedang Diproses").
  * 🔑 **Generate Key** (Khusus Admin untuk menerbitkan lisensi bagi pembuat tiket).
  * 🔒 **Tutup Ticket** (Untuk menghapus channel dan memperbarui status di database).

### C. Tiket Pembelian Lisensi (`tkt_type_purchase`)
* Bot membuat channel privat `ticket-XXXXX`.
* Laravel Backend secara otomatis membuat transaksi ke **Midtrans Core API** dengan metode pembayaran **QRIS (GoPay)**.
* Di dalam channel tiket, bot memposting **Invoice Detail** beserta gambar **QRIS QR Code** agar user bisa langsung melakukan scan dan membayar dari handphone mereka.
* Menyediakan tombol:
  * 🔍 **Cek Pembayaran** (Mengonfirmasi status transaksi secara langsung).
  * 🔄 **Proses Ticket** (Staff Admin fallback).
  * 🔒 **Tutup Ticket**.

---

## 3. Cara Kerja Mode Simulasi & Integrasi Midtrans

Sistem dirancang untuk mendukung **Zero-Setup Testing** (Pengujian tanpa memerlukan akun Midtrans) sekaligus siap untuk digunakan di **Production**.

### A. Pengaturan Environment (`Backend/.env`)
Tambahkan variabel berikut di file `.env` Laravel Anda:
```env
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_LICENSE_PRICE=50000
```

### B. Mode Simulasi (Sangat Mudah untuk Development)
Jika `MIDTRANS_SERVER_KEY` dibiarkan kosong:
1. Sistem akan menghasilkan QR Code dummy berisi teks instruksi simulasi.
2. Ketika user mengklik tombol **🔍 Cek Pembayaran**, sistem secara otomatis menganggap pembayaran **sukses**.
3. Sistem menerbitkan lisensi 30 hari untuk user tersebut, menampilkan key lisensi di channel tiket, dan mengubah tombol menjadi hanya **🔒 Tutup Ticket**.

### C. Mode Integrasi Riil (Sandbox / Production)
Jika `MIDTRANS_SERVER_KEY` diisi dengan server key dari Midtrans Dashboard Anda:
1. Sistem memanggil API resmi Midtrans Sandbox/Production untuk menghasilkan kode QR QRIS asli.
2. Ketika user mengklik **🔍 Cek Pembayaran**, backend Laravel akan menanyakan status pembayaran ke API Midtrans. Jika transaksi berstatus `settlement` atau `capture`, pembayaran dianggap lunas dan lisensi diterbitkan secara instan.
3. Anda juga dapat mendaftarkan Webhook Callback URL di dashboard Midtrans agar status terupdate otomatis tanpa klik tombol:
   ```
   https://[domain-ngrok-anda]/api/midtrans/callback
   ```

---

## 4. Analisis Masalah Koneksi Bot ke Laravel Backend

Jika bot sempat memunculkan pesan error `❌ Gagal terhubung ke Laravel Backend: OK` saat pertama kali dijalankan:

* **Penyebab Utama:** Terjadinya *syntax error* di file konfigurasi PHP (`Backend/config/services.php`) atau error PHP lainnya pada bootstrap aplikasi. Karena Laravel mengembalikan kode status HTTP 200 namun dengan isi output berupa error HTML/teks (bukan JSON terformat), Axios pada bot gagal melakukan *parsing* object JSON `response.data`, sehingga `response.data?.status` bernilai `undefined` (dievaluasi sebagai gagal/`success: false`) dengan pesan default `'OK'`.
* **Solusi yang Diterapkan:** Kami telah memperbaiki penulisan konfigurasi di `config/services.php` dan memastikan endpoint `/api/bot/health` terproteksi dengan middleware `bot.auth` secara benar.
* **Hasil Uji:** Bot sekarang terhubung 100% sukses dengan output log:
  ```
  ✅ Terhubung ke Laravel Backend: http://nginx
  ```
  Komunikasi internal container menggunakan alamat `http://nginx` (resolusi nama service internal Docker bridge network) berjalan lancar dan aman.
