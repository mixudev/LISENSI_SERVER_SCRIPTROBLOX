# 🎮 Panduan Integrasi Roblox Account Binding

Dokumen ini menjelaskan alur pengaitan (binding) akun Roblox ke Discord, penyusunan Redirect URI untuk Roblox OAuth, serta penggunaan mode fallback input manual.

---

## 1. Alur Pengaitan Akun (Roblox OAuth2)

Roblox OAuth2 digunakan agar user dapat melakukan autentikasi akun Roblox mereka dengan aman menggunakan otorisasi satu klik.

### Cara Konfigurasi OAuth:
1. Buka [Roblox Creator Dashboard](https://create.roblox.com/dashboard/credentials).
2. Buat kredensial OAuth2 baru untuk aplikasi Anda.
3. Daftarkan **Redirect URI** berikut pada aplikasi Roblox Anda (ganti domain dengan domain statis ngrok Anda):
   ```
   https://unfertile-proconsularly-dorris.ngrok-free.dev/roblox/callback
   ```
4. Dapatkan `Client ID` dan `Client Secret` dari Roblox Creator Dashboard.
5. Masukkan ke file `.env` Laravel Backend (`Backend/.env`):
   ```env
   ROBLOX_CLIENT_ID=1234567890123456789
   ROBLOX_CLIENT_SECRET=roblox_sec_xxxxxxxxxxxxxxxx
   ```

---

## 2. Mode Fallback: Input Username Manual (Sangat Direkomendasikan untuk Development)

Jika Anda ingin melakukan pengujian fitur pengaitan akun Roblox dengan cepat tanpa perlu mendaftarkan aplikasi Roblox OAuth atau mengonfigurasi *Redirect URI*:

1. **Kosongkan** nilai `ROBLOX_CLIENT_ID` di file `Backend/.env`:
   ```env
   ROBLOX_CLIENT_ID=
   ROBLOX_CLIENT_SECRET=
   ```
2. Ketika user mengklik tombol **Kaitkan Akun** di Discord, sistem backend akan mendeteksi bahwa integrasi OAuth tidak diaktifkan.
3. Bot akan mengarahkan user ke halaman **Input Username Manual** di website dashboard.
4. User cukup memasukkan username Roblox mereka. Backend akan menanyakan keberadaan akun tersebut langsung ke API publik resmi Roblox (`users.roblox.com`).
5. Jika username valid, akun Roblox berhasil dikaitkan dengan akun Discord user secara instan tanpa perlu alur login OAuth yang rumit.
