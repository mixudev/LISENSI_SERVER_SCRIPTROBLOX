1. Pendahuluan
1.1 Tujuan
Membangun sistem License Server berbasis Laravel 12 yang mampu:
•	Generate License Key otomatis dengan format kriptografis aman
•	Validasi License Key real-time dengan dukungan Redis Cache
•	Binding HWID satu perangkat per lisensi
•	Reset HWID oleh pengguna dengan batasan yang dapat dikonfigurasi per produk
•	Mengelola masa aktif lisensi dengan auto-expiry
•	Mengelola banyak produk dengan konfigurasi berbeda-beda
•	Menyediakan Landing Page publik, Dashboard Admin, dan Dashboard User
•	Mendukung ribuan pengguna secara bersamaan dengan performa tinggi

1.2 Ruang Lingkup
Sistem ini terdiri dari tiga area utama:
•	Landing Page — halaman publik untuk informasi produk dan pembelian lisensi
•	Dashboard Admin — panel kontrol penuh untuk admin mengelola lisensi, user, dan produk
•	Dashboard User — panel mandiri bagi pengguna untuk mengelola lisensi mereka sendiri
•	API Endpoint — endpoint REST untuk validasi dan aktivasi lisensi dari aplikasi eksternal

1.3 Teknologi
Layer	Teknologi
Backend	Laravel 12, PHP 8.4
Frontend	Blade, Livewire 3, Alpine.js, Tailwind CSS 4
Database	MySQL / PostgreSQL
Cache & Queue	Redis (Cache + Queue Worker)
Storage	Local Storage (S3 opsional)
Deployment	Ubuntu, Nginx, PHP-FPM, Supervisor, SSL Let's Encrypt

 
2. Arsitektur Sistem
2.1 Gambaran Umum
Sistem terbagi menjadi empat lapisan utama:
•	Presentation Layer — Landing Page, Dashboard Admin, Dashboard User (Blade + Livewire)
•	Application Layer — Laravel Controller, Service Layer, FormRequest
•	Infrastructure Layer — MySQL, Redis Cache, Redis Queue, Local Storage
•	External — Aplikasi klien yang melakukan request ke API endpoint

2.2 Komponen Utama
Komponen	Fungsi
LicenseService	Generate, aktivasi, validasi, suspend, ban lisensi
HwidService	Binding dan reset HWID dengan validasi limit
ApiLogService	Pencatatan semua request API via Queue
Redis Cache	Cache license data TTL 5 menit, invalidate saat update
Queue Worker	Async logging, email notifikasi expired, statistik

 
3. Alur Sistem
3.1 Flow Pembelian Lisensi
1. User membuka Landing Page dan memilih produk
2. Admin menerima pesanan dan membuat lisensi melalui Dashboard Admin
3. Sistem generate License Key dengan format: LZD-XXXX-XXXX-XXXX-XXXX
4. Key disimpan ke database dengan status active dan expired_at sesuai produk
5. Key diberikan kepada user (manual atau notifikasi email)
6. User login Dashboard User untuk melihat license mereka

3.2 Flow Aktivasi HWID
1. Aplikasi user mengirim request POST /api/license/activate dengan key dan HWID
2. Server cek Redis Cache terlebih dahulu, jika miss baru query MySQL
3. Validasi: key exist, status active, belum expired
4. Jika HWID null (belum terikat): update HWID, set activated_at, return success
5. Jika HWID sudah ada: bandingkan HWID kiriman dengan database
6. Jika cocok: update last_used_at, last_ip, return success
7. Jika tidak cocok: return HWID_MISMATCH (403)
8. Semua response dicatat ke api_logs via Queue (async)

3.3 Flow Reset HWID
1. User login Dashboard User
2. Klik tombol Reset HWID pada lisensi yang diinginkan
3. Sistem cek canResetHwid(): batas total dan interval waktu dari konfigurasi produk
4. Jika diizinkan: hwid = null, hwid_reset_count++, hwid_last_reset_at = now()
5. Catat ke hwid_reset_logs dan license_activities
6. Invalidate Redis Cache untuk key tersebut
7. Perangkat baru dapat melakukan binding ulang
 
4. Struktur Database
4.1 Tabel users
Kolom	Tipe	Keterangan
id	BIGINT PK	Auto increment
name	VARCHAR(255)	Nama lengkap
email	VARCHAR(255) UNIQUE	Email login
password	VARCHAR(255)	Bcrypt hash
role	ENUM(admin, user)	Peran akun
is_active	BOOLEAN	Status akun aktif/nonaktif
email_verified_at	TIMESTAMP	Waktu verifikasi email
created_at / updated_at	TIMESTAMP	Audit waktu
deleted_at	TIMESTAMP	Soft delete

4.2 Tabel products
Kolom	Tipe	Keterangan
id	BIGINT PK	Auto increment
name	VARCHAR(255)	Nama produk: Main, VIP, dll
slug	VARCHAR(255) UNIQUE	URL-friendly identifier
description	TEXT	Deskripsi produk
version	VARCHAR(20)	Versi script/produk
script_path	VARCHAR(255)	Path ke file script di storage/
license_duration_days	INT UNSIGNED	Durasi lisensi default (hari)
max_hwid_resets	TINYINT	Batas reset HWID seumur hidup
hwid_reset_interval_days	TINYINT	Interval minimum antar reset (hari)
price	DECIMAL(10,2)	Harga produk
status	ENUM(active, inactive, maintenance)	Status produk

 
4.3 Tabel licenses
Kolom	Tipe	Keterangan
id	BIGINT PK	
user_id	BIGINT FK	Pemilik lisensi (nullable)
product_id	BIGINT FK	Produk terkait (required)
license_key	VARCHAR(30) UNIQUE	Format: LZD-XXXX-XXXX-XXXX-XXXX
hwid	VARCHAR(255)	Hardware ID perangkat terikat
hwid_reset_count	TINYINT	Jumlah reset yang sudah dilakukan
hwid_last_reset_at	TIMESTAMP	Waktu reset HWID terakhir
status	ENUM(active, expired, banned, suspended)	Status lisensi
expired_at	TIMESTAMP	Null = lifetime license
ban_reason	TEXT	Alasan ban/suspend oleh admin
last_ip	VARCHAR(45)	IP terakhir (IPv4 & IPv6)
last_used_at	TIMESTAMP	Waktu penggunaan terakhir
activated_at	TIMESTAMP	Kapan pertama kali diaktifkan
created_by	BIGINT FK	Admin yang membuat lisensi
notes	TEXT	Catatan internal admin

4.4 Tabel Pendukung
•	hwid_reset_logs — mencatat setiap reset HWID: license_id, old_hwid, new_hwid, reset_by, admin_id, ip, reason
•	api_logs — log seluruh request API: endpoint, method, ip, user_agent, license_key_used, status, http_code, response_time_ms
•	license_activities — aktivitas user di dashboard: action, user_id, license_id, meta (JSON), ip
 
5. API Endpoint
5.1 Aktivasi Lisensi
Method & URL
POST /api/license/activate
Request Body (JSON)
{ "key": "LZD-XXXX-XXXX-XXXX-XXXX", "hwid": "HASH12345ABC" }
Response Sukses (200)
{ "status": true, "message": "Activated" }
Response Gagal (403)
{ "status": false, "message": "HWID mismatch" }

5.2 Validasi Lisensi
Method & URL
POST /api/license/check
Request Body (JSON)
{ "key": "LZD-XXXX-XXXX-XXXX-XXXX", "hwid": "HASH12345ABC" }
Response Sukses (200)
{ "status": true }

5.3 Reset HWID via API
Method & URL
POST /api/license/reset
Request Body (JSON)
{ "key": "LZD-XXXX-XXXX-XXXX-XXXX" }
Catatan
Endpoint ini memerlukan autentikasi Bearer Token milik pemilik lisensi.

5.4 Kode Status API
HTTP Code	Status	Kondisi
200	success	Request berhasil
401	unauthorized	Token tidak valid atau tidak ada
403	forbidden	HWID mismatch / license banned / suspended
404	not_found	License key tidak ditemukan
410	expired	License sudah melewati masa aktif
429	rate_limited	Melebihi 60 request per menit
500	server_error	Kesalahan internal server

 
6. UI — Landing Page
Landing Page adalah halaman publik yang dapat diakses tanpa login. Dibangun dengan Blade + Alpine.js + Tailwind CSS. Tujuannya memperkenalkan produk dan mendorong pembelian lisensi.

6.1 Navbar (Sticky)
Navigasi tetap menempel di atas saat scroll. Berisi:
•	Logo sistem di kiri (teks atau gambar)
•	Menu navigasi tengah: Beranda, Produk, Fitur, Cara Kerja, Kontak
•	Tombol kanan: Login (outline) dan Daftar (filled primary)
•	Versi mobile: hamburger menu yang membuka side drawer

6.2 Section Hero
Bagian pembuka halaman berisi:
•	Headline utama: judul besar 2–3 baris yang menjelaskan manfaat utama sistem
•	Subheadline: 1–2 kalimat deskripsi singkat
•	Dua tombol CTA: "Lihat Produk" (scroll ke section produk) dan "Dokumentasi API"
•	Background: ilustrasi atau gradient pattern, bukan foto

6.3 Section Fitur Unggulan
Grid 3 kolom (desktop) / 1 kolom (mobile), tiap card berisi:
•	Ikon SVG representatif
•	Judul fitur singkat
•	Deskripsi 2–3 kalimat
Konten card yang wajib ada:
→  Proteksi HWID — Lisensi terikat ke satu perangkat, tidak bisa dibagikan
→  Multi Produk — Kelola banyak produk dari satu dashboard
→  API Cepat — Response < 200ms dengan Redis Cache
→  Reset HWID — Pengguna dapat pindah perangkat sesuai batas yang ditentukan
→  Dashboard Lengkap — Panel admin dan user yang terpisah dan mudah digunakan
→  Audit Log — Semua aktivitas tercatat rinci

6.4 Section Produk & Harga
Kartu harga berjajar horizontal (3–4 kolom desktop, scroll horizontal mobile). Satu kartu berisi:
•	Nama produk (contoh: Main, Premium, VIP, Ultimate)
•	Harga per bulan
•	Daftar fitur dengan centang hijau
•	Durasi lisensi default
•	Batas reset HWID
•	Tombol "Beli Sekarang" — mengarah ke halaman kontak atau WhatsApp admin
Kartu yang direkomendasikan diberi label badge "Populer" dan border highlight.

6.5 Section Cara Kerja
Langkah-langkah dalam format timeline horizontal (desktop) atau vertikal (mobile):
→  Langkah 1 — Beli lisensi dan dapatkan License Key
→  Langkah 2 — Login ke Dashboard User
→  Langkah 3 — Masukkan License Key di aplikasi Anda
→  Langkah 4 — HWID terikat otomatis saat pertama kali digunakan
→  Langkah 5 — Aplikasi berjalan selama lisensi masih aktif

6.6 Section Statistik
Baris angka-angka untuk membangun kepercayaan (dapat diedit oleh admin):
•	Total Lisensi Aktif
•	Total Pengguna
•	Uptime API
•	Total Produk

6.7 Section FAQ
Accordion (klik untuk buka/tutup) berisi pertanyaan umum:
→  Apa itu HWID? — Hardware ID, pengenal unik perangkat Anda
→  Berapa kali saya bisa reset HWID? — Tergantung paket yang dibeli
→  Apakah lisensi bisa digunakan di banyak PC? — Tidak, satu lisensi satu perangkat
→  Bagaimana cara perpanjang lisensi? — Login dashboard lalu klik perpanjang
→  Apakah ada refund? — Hubungi admin melalui kontak yang tersedia

6.8 Footer
Grid 3 kolom berisi:
•	Kolom 1 — Logo, deskripsi singkat sistem, ikon media sosial
•	Kolom 2 — Link cepat: Beranda, Produk, API Docs, Kontak
•	Kolom 3 — Kontak: email, WhatsApp (opsional)
Baris bawah: copyright dan link Kebijakan Privasi + Syarat Penggunaan.
 
7. UI — Dashboard Admin
Dashboard Admin hanya dapat diakses oleh user dengan role = admin. Dibangun dengan Blade + Livewire + Tailwind CSS. Tidak menggunakan package admin seperti Filament — semua UI dibuat custom.

7.1 Layout Global Admin
•	Sidebar kiri — lebar 260px, sticky, berisi logo dan menu navigasi
•	Topbar — tinggi 64px, berisi tombol buka/tutup sidebar (mobile), nama admin, notifikasi, tombol logout
•	Content area — area kanan, padding konsisten, background abu muda
•	Sidebar collapse — di layar < 1024px sidebar tersembunyi, buka via hamburger di topbar

7.2 Menu Sidebar Admin
•	Dashboard — ringkasan statistik utama
•	Lisensi — daftar, cari, kelola semua lisensi
•	Produk — daftar dan kelola produk
•	Pengguna — daftar user terdaftar
•	Log API — riwayat request masuk
•	Aktivitas — riwayat aktivitas user
•	Pengaturan — konfigurasi sistem (opsional)

7.3 Halaman Dashboard (Beranda Admin)
Halaman pertama setelah login admin. Berisi:
•	Baris Stat Card (4 kartu): Total Lisensi Aktif, Total User, Request Hari Ini, Lisensi Akan Expired (7 hari ke depan)
•	Grafik garis — request API per hari dalam 30 hari terakhir (menggunakan library Chart.js via CDN)
•	Grafik donat — distribusi status lisensi: active, expired, banned, suspended
•	Tabel Recent Activity — 10 aktivitas terbaru dengan kolom: waktu, user, aksi, lisensi
•	Tabel Lisensi Hampir Expired — 5 lisensi terdekat expired dengan tombol perpanjang cepat

7.4 Halaman Manajemen Lisensi
URL: /admin/licenses
•	Filter bar di atas tabel: dropdown Status (all/active/expired/banned/suspended), dropdown Produk, input pencarian key/email, tombol Reset Filter
•	Tombol aksi di atas tabel: "Generate Lisensi" (1 key) dan "Generate Banyak" (bulk, input jumlah)
•	Tabel data dengan kolom:
→  License Key — teks monospace, tombol salin
→  User — nama dan email pemilik (atau "-" jika belum ada pemilik)
→  Produk — nama produk
→  Status — badge berwarna (hijau=active, kuning=suspended, merah=banned, abu=expired)
→  HWID — tampilkan 8 karakter pertama + "..." atau "-" jika kosong
→  Expired — tanggal atau "Lifetime"
→  Terakhir Digunakan — tanggal relatif
→  Aksi — tombol dropdown: Detail, Edit Expired, Reset HWID, Suspend, Ban, Hapus
•	Pagination — 25 item per halaman, dengan info "Menampilkan X dari Y lisensi"
•	Tombol Export CSV — mengunduh hasil filter saat ini

7.5 Modal Generate Lisensi
Muncul sebagai modal overlay (Livewire component). Berisi form:
•	Dropdown Produk — wajib diisi
•	Input Assign ke User — autocomplete email (opsional, bisa diassign nanti)
•	Input Durasi (hari) — pre-filled dari produk, bisa di-override
•	Input Catatan Admin — textarea opsional
•	Tombol Generate — submit dan tampilkan key yang dihasilkan
•	Untuk Generate Banyak: tambah input Jumlah (2–100) dan tampilkan tabel hasil key setelah generate

7.6 Modal Edit Lisensi
Form edit lisensi yang sudah ada. Berisi:
•	Informasi hanya baca: License Key, Produk, Dibuat oleh
•	Input Expired At — date picker
•	Dropdown Status — active / suspended / banned
•	Textarea Ban Reason — muncul jika status = banned atau suspended
•	Dropdown Assign User — autocomplete email
•	Tombol Reset HWID — di dalam modal dengan konfirmasi
•	Tombol Simpan Perubahan

7.7 Halaman Manajemen Produk
URL: /admin/products
•	Tabel produk: Nama, Slug, Versi, Durasi Default, Batas Reset HWID, Interval Reset, Harga, Status, Aksi
•	Tombol Tambah Produk — buka modal form
•	Form produk: Nama, Deskripsi, Versi, Durasi (hari), Batas Reset HWID, Interval Reset (hari), Harga, Upload file script, Status
•	Tombol Hapus — dengan konfirmasi, tidak bisa hapus jika masih ada lisensi aktif

7.8 Halaman Manajemen User
URL: /admin/users
•	Tabel user: Nama, Email, Role, Status Akun, Jumlah Lisensi, Terdaftar, Aksi
•	Aksi per user: Lihat Detail, Nonaktifkan Akun, Reset Password
•	Halaman detail user: info akun + daftar semua lisensi milik user + riwayat aktivitas

7.9 Halaman Log API
URL: /admin/api-logs
•	Filter: Endpoint, Status, IP, Range Tanggal
•	Tabel: Waktu, Endpoint, IP, Key Digunakan, Status, HTTP Code, Response Time (ms)
•	Highlight baris merah untuk request gagal, kuning untuk response lambat (> 200ms)
•	Statistik ringkas di atas tabel: Total request hari ini, Request sukses, Request gagal, Rata-rata response time

7.10 Halaman Aktivitas User
URL: /admin/activities
•	Tabel: Waktu, User, Aksi, Lisensi Terkait, IP, Detail (meta)
•	Filter: User, Jenis Aksi, Range Tanggal
 
8. UI — Dashboard User
Dashboard User diakses oleh pengguna biasa (role = user). Tampilan lebih sederhana dan berfokus pada informasi lisensi milik user tersebut. Dibangun dengan Blade + Livewire + Tailwind CSS.

8.1 Layout Global User
•	Navbar atas — logo di kiri, nama user + tombol logout di kanan
•	Sidebar kiri (opsional, bisa full topbar) — menu navigasi ringkas
•	Content area — area utama yang luas
•	Responsive penuh — mobile-first

8.2 Menu Navigasi User
•	Beranda — ringkasan lisensi saya
•	Lisensi Saya — daftar semua lisensi
•	Riwayat — log aktivitas akun
•	Profil — edit info akun

8.3 Halaman Beranda User
Halaman pertama setelah user login. Berisi:
•	Ucapan selamat datang dengan nama user
•	Stat card ringkas: Lisensi Aktif, Lisensi Expired, Lisensi Akan Expired (7 hari)
•	Daftar Lisensi Aktif Terbaru — maksimal 3 kartu, dengan tombol "Lihat Semua"
•	Notifikasi inline jika ada lisensi yang akan expired dalam 7 hari

8.4 Halaman Lisensi Saya
URL: /user/licenses
•	Filter sederhana: semua / aktif / expired
•	Tampilan kartu (card grid), bukan tabel — lebih mudah dibaca di mobile
Tiap kartu lisensi berisi:
→  Header kartu — nama produk + badge status berwarna
→  License Key — teks monospace besar, tombol salin dengan feedback "Tersalin!"
→  HWID — tampilkan sebagian atau "Belum terikat" jika kosong
→  Expired — tanggal lengkap atau "Seumur Hidup" — dengan warna merah jika < 7 hari
→  Terakhir digunakan — tanggal relatif
→  Tombol Reset HWID — tampil jika masih bisa reset, dengan sisa limit dan waktu cooldown
→  Tombol Download Script — jika produk memiliki file script dan lisensi masih aktif
→  Tombol Perpanjang Lisensi — arahkan ke halaman kontak admin / WhatsApp

8.5 Modal Konfirmasi Reset HWID
Muncul saat user klik tombol Reset HWID. Berisi:
•	Peringatan: "Setelah reset, perangkat lama tidak bisa digunakan sampai HWID baru terikat"
•	Informasi sisa reset yang tersisa dan waktu cooldown berikutnya
•	Tombol Konfirmasi Reset dan Batal

8.6 Halaman Riwayat Aktivitas
URL: /user/activities
•	Tampilan timeline vertikal — lebih intuitif dari tabel untuk riwayat personal
•	Tiap item: ikon aksi + deskripsi aksi + waktu + IP address
•	Filter: jenis aksi, range tanggal
•	Aksi yang ditampilkan: login, logout, lihat lisensi, reset HWID, download script, perpanjang lisensi

8.7 Halaman Profil
URL: /user/profile
•	Form edit: Nama, Email (tidak bisa diubah jika sudah terverifikasi), No. Telepon
•	Tombol Ganti Password — form terpisah: password lama, password baru, konfirmasi
•	Tidak ada fitur delete akun dari sisi user — harus minta admin
 
9. Autentikasi & Otorisasi
9.1 Halaman Login
URL: /login
•	Form: Email dan Password
•	Tombol "Ingat Saya" (remember_token)
•	Link Lupa Password
•	Setelah login berhasil: redirect ke /admin/dashboard (admin) atau /user/dashboard (user)

9.2 Halaman Register
URL: /register
•	Form: Nama, Email, Password, Konfirmasi Password
•	Role selalu = user. Admin hanya bisa dibuat manual atau oleh admin yang sudah ada
•	Verifikasi email opsional (bisa dikonfigurasi)

9.3 Halaman Lupa Password
•	Input Email → kirim link reset via Queue (email)
•	Halaman reset password dengan token dari URL

9.4 Middleware Proteksi
•	auth — proteksi semua route dashboard
•	role:admin — proteksi semua route /admin/*
•	verified — opsional untuk verifikasi email
•	throttle:60,1 — rate limiter 60 request per menit untuk semua route /api/*
 
10. Keamanan
10.1 HTTPS
Semua request wajib HTTPS. HTTP akan redirect otomatis ke HTTPS via Nginx.

10.2 Rate Limiting
60 request per menit per IP menggunakan Laravel RateLimiter dengan backend Redis. Response 429 jika terlampaui.

10.3 License Key Generator
Menggunakan random_bytes() PHP (kriptografis aman). Format: LZD-XXXX-XXXX-XXXX-XXXX. Panjang minimal 28 karakter alfanumerik.

10.4 Redis Cache
Setiap validasi lisensi di-cache selama 5 menit dengan key "license:{license_key}". Cache diinvalidate otomatis saat data lisensi diupdate.

10.5 Queue untuk Logging
Semua pencatatan api_logs dilakukan via Queue Job agar tidak menambah latensi response API.

10.6 File Script Aman
File script (.lua, dll) disimpan di storage/app/scripts/ — di luar folder public. Akses hanya melalui controller yang sudah memvalidasi lisensi aktif.

10.7 CSRF Protection
Semua form dan Livewire component terlindungi CSRF token bawaan Laravel.
 
11. Target Performa
Metrik	Target
Jumlah Lisensi	10.000+
User Aktif Bersamaan	5.000+
Request API per Detik	500+
Response API (p95)	< 200 ms
Cache Hit Rate	> 80%
Uptime Target	99.9%

 
12. Roadmap Pengembangan
Fase 1 — Foundation (Selesai)
•	Migration & Model: users, products, licenses, hwid_reset_logs, api_logs, license_activities

Fase 2 — Backend Core
•	Service Layer: LicenseService, HwidService, ApiLogService
•	Repository: LicenseRepository, ProductRepository
•	API Controller + FormRequest + middleware Rate Limiter
•	Redis Cache integration
•	Queue Jobs: LogApiRequestJob, SendExpiryNotificationJob

Fase 3 — Frontend
•	Landing Page: Navbar, Hero, Fitur, Harga, Cara Kerja, FAQ, Footer
•	Autentikasi: Login, Register, Lupa Password
•	Dashboard Admin: semua halaman (section 7)
•	Dashboard User: semua halaman (section 8)

Fase 4 — Security & Deployment
•	HTTPS enforcer, Rate limit middleware, Audit trail
•	Konfigurasi Nginx, PHP-FPM, Supervisor, Redis
•	SSL Let's Encrypt
•	Feature testing: aktivasi, validasi, HWID, expired, rate limit, ban/suspend

Fase 5 — Future Roadmap
•	Multi-device license support
•	Webhook notification
•	Payment gateway integration
•	Telegram Bot notifikasi
•	Auto-renewal subscription
•	Two-Factor Authentication (2FA)
•	CDN untuk download file
•	Auto-ban aktivitas mencurigakan
