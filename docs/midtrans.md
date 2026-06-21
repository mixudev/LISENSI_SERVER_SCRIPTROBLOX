# 💳 Panduan Integrasi Midtrans Payment Gateway & QRIS

Dokumen ini menjelaskan alur pembayaran pembelian lisensi otomatis menggunakan Midtrans, cara kerja mode simulasi (Zero-Setup), dan konfigurasi mode riil.

---

## 1. Pengaturan Environment (`Backend/.env`)

Kunci konfigurasi Midtrans diatur di file `.env` Laravel Backend:

```env
# Server Key & Client Key diperoleh dari Dashboard Midtrans
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxx

# Set ke false untuk testing (Sandbox), set ke true jika sudah rilis (Production)
MIDTRANS_IS_PRODUCTION=false

# Harga lisensi per 30 hari (dalam Rupiah)
MIDTRANS_LICENSE_PRICE=50000
```

---

## 2. Mode Simulasi (Zero-Setup Development)

Jika Anda belum memiliki akun Midtrans atau ingin melakukan pengujian alur tiket pembelian secara cepat tanpa memanggil API Midtrans:

1. **Kosongkan** nilai `MIDTRANS_SERVER_KEY` di file `.env` Backend.
2. Ketika user mengklik kategori **Pembelian Lisensi** di channel tiket:
   - Bot akan membuat channel tiket baru (`ticket-XXXXX`).
   - Bot memposting invoice detail beserta **QR Code Simulasi** (berisi teks edukatif bahwa ini adalah mode simulasi).
3. Ketika user mengklik tombol **🔍 Cek Pembayaran**:
   - Sistem secara otomatis menganggap pembayaran **sukses**.
   - Sistem menerbitkan lisensi aktif 30 hari untuk Discord ID user tersebut.
   - Key lisensi dikirim langsung ke channel tiket.
   - Pilihan tombol tiket diubah menjadi hanya **🔒 Tutup Ticket**.

---

## 3. Mode Riil (Sandbox / Production)

Jika Anda ingin mencoba alur pembayaran QRIS yang sesungguhnya:

1. Masukkan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` yang valid di `.env` Backend.
2. Saat tiket pembelian dibuka:
   - Laravel Backend akan melakukan request API ke Midtrans untuk membuat transaksi QRIS asli (GoPay/ShopeePay).
   - Midtrans mengembalikan URL QR Code asli.
   - Bot mengambil QR Code asli tersebut dan menampilkannya sebagai gambar di channel tiket agar bisa di-scan oleh e-wallet user.
3. User dapat melakukan pembayaran menggunakan aplikasi GoPay/OVO/Dana di handphone.
4. Ketika user mengklik **🔍 Cek Pembayaran**, bot akan menanyakan status pembayaran transaksi tersebut ke API Midtrans. Jika transaksi berstatus `settlement` (Lunas), lisensi akan diterbitkan seketika.
5. **Webhook Callback (Opsional tapi Direkomendasikan):**
   Agar pembayaran langsung terdeteksi otomatis tanpa perlu mengklik tombol "Cek Pembayaran", daftarkan URL callback ini di dashboard Midtrans Anda:
   ```
   https://unfertile-proconsularly-dorris.ngrok-free.dev/api/midtrans/callback
   ```
