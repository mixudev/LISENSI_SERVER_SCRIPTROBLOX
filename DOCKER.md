# Docker — SCRIPT_LISENSI

Panduan menjalankan stack lengkap (Laravel Backend, MySQL, Queue Worker, Nginx, Discord Bot, ngrok) menggunakan Docker Compose.

## Prasyarat

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) atau Docker Engine + Compose v2
- **Windows:** jalankan script bash di **Git Bash** atau **WSL** (`chmod +x` tidak wajib di Git Bash)
- Akun [ngrok](https://ngrok.com/) (gratis) jika ingin testing Roblox executor dari luar jaringan lokal

## Arsitektur

```
Browser / Roblox  ──►  nginx (:8000)  ──►  backend (PHP-FPM)
                              │                    │
                              │                    ├──► mysql
                              │                    └──► queue worker
Bot Discord (sl-bot) ──► http://nginx/api/bot/*  (internal Docker)
ngrok (opsional)     ──► nginx:80  (URL publik untuk Roblox)
```

| Service | Container | Port Host | Fungsi |
|---------|-----------|-----------|--------|
| `mysql` | sl-mysql | 3306 | Database MySQL 8 |
| `backend` | sl-backend | — (internal) | Laravel PHP-FPM |
| `queue` | sl-queue | — | `php artisan queue:work` |
| `nginx` | sl-nginx | 8000 | Web server + static files |
| `bot` | sl-bot | — | Discord bot (Node.js) |
| `ngrok` | sl-ngrok | 4040 | Tunnel publik (profile opsional) |

File env **tidak** dibake ke image — di-mount dari host:

- `.env` (root) — variabel Docker Compose
- `Backend/.env` — konfigurasi Laravel
- `Bot_Server/.env` — konfigurasi Discord bot

Asset frontend Vite disalin dari image ke `Backend/public/build/` agar nginx (mount host) bisa melayani CSS/JS.

---

## Quick Start (Pertama Kali)

```bash
# 1. Clone & masuk ke folder project
cd SCRIPT_LISENSI

# 2. Setup pertama kali (build image, migrate DB, generate APP_KEY)
chmod +x setup.sh start.sh stop.sh   # Linux/macOS
./setup.sh

# 3. Isi kredensial Discord di Bot_Server/.env
#    DISCORD_TOKEN, CLIENT_ID, DASHBOARD_CHANNEL_ID, ADMIN_ROLE_ID

# 4. (Opsional) Isi NGROK_AUTHTOKEN di .env root untuk testing Roblox

# 5. Jalankan stack
./start.sh
```

Akses aplikasi:

- **Lokal:** http://localhost:8000
- **Admin dashboard:** http://localhost:8000/admin/dashboard
- **User dashboard:** http://localhost:8000/user/dashboard
- **ngrok dashboard:** http://localhost:4040 (jika ngrok aktif)

---

## Perintah Sehari-hari

```bash
# Jalankan semua service (+ ngrok jika NGROK_AUTHTOKEN terisi)
./start.sh

# Stop semua container (termasuk ngrok)
./stop.sh

# Lihat log
docker compose -f docker-compose.yml -f docker-compose.local.yml logs -f
docker compose -f docker-compose.yml -f docker-compose.local.yml logs -f bot
docker compose -f docker-compose.yml -f docker-compose.local.yml logs -f backend
docker compose -f docker-compose.yml -f docker-compose.local.yml logs -f nginx

# Restart service tertentu
docker compose -f docker-compose.yml -f docker-compose.local.yml restart bot
docker compose -f docker-compose.yml -f docker-compose.local.yml restart backend queue

# Artisan di dalam container
docker compose -f docker-compose.yml -f docker-compose.local.yml exec backend php artisan migrate --force
docker compose -f docker-compose.yml -f docker-compose.local.yml exec backend php artisan config:clear
docker compose -f docker-compose.yml -f docker-compose.local.yml exec backend php artisan cache:clear
docker compose -f docker-compose.yml -f docker-compose.local.yml exec backend php artisan db:seed

# Cek status container
docker compose -f docker-compose.yml -f docker-compose.local.yml ps
```

---

## Variabel Environment

### 1. Root `.env` (dari `.env.docker.example`)

| Variabel | Default | Keterangan |
|----------|---------|------------|
| `APP_PORT` | 8000 | Port host → nginx |
| `MYSQL_PORT` | 3306 | Port host → MySQL |
| `NGROK_UI_PORT` | 4040 | Web UI ngrok |
| `MYSQL_ROOT_PASSWORD` | — | Password root MySQL |
| `MYSQL_DATABASE` | script_lisensi | Nama database |
| `MYSQL_USER` / `MYSQL_PASSWORD` | — | User aplikasi |
| `DISCORD_BOT_API_TOKEN` | auto-generate | Token shared Bot ↔ Laravel |
| `NGROK_AUTHTOKEN` | — | Token ngrok (wajib untuk tunnel Roblox) |
| `BACKEND_BUILD_TARGET` | production | `production` atau `development` |

### 2. `Backend/.env`

**Auto-sync oleh script** (`setup.sh` / `start.sh`):

- `DB_HOST=mysql`, `DB_*` credentials
- `DISCORD_BOT_API_TOKEN` (sama dengan root `.env`)
- `APP_URL` → `http://localhost:8000` atau URL ngrok

**Isi manual:**

- `APP_KEY` (dibuat otomatis saat setup)
- `DISCORD_BOT_ADMIN_IDS`
- `ADMIN_CONTACT_EMAIL`, `ADMIN_CONTACT_WHATSAPP` (tombol perpanjang lisensi)

### 3. `Bot_Server/.env`

**Auto-sync:**

- `LARAVEL_API_TOKEN` = `DISCORD_BOT_API_TOKEN`
- `LARAVEL_API_URL` = **`http://nginx`** (selalu internal Docker)

**Isi manual:**

- `DISCORD_TOKEN`, `CLIENT_ID`
- `DASHBOARD_CHANNEL_ID`, `ADMIN_ROLE_ID`

### Penting: APP_URL vs LARAVEL_API_URL

| Variabel | Nilai | Digunakan untuk |
|----------|-------|-----------------|
| `APP_URL` (Backend) | URL ngrok / localhost | Link publik, Loader.lua Roblox |
| `LARAVEL_API_URL` (Bot) | `http://nginx` | Bot memanggil `/api/bot/*` di dalam Docker |

**Jangan** set `LARAVEL_API_URL` ke URL ngrok — ngrok bisa mati/berubah subdomain dan bot akan error HTTP 404.

---

## ngrok & Testing Roblox

1. Daftar di https://dashboard.ngrok.com/get-started/your-authtoken
2. Salin token ke `NGROK_AUTHTOKEN` di `.env` root
3. Jalankan `./start.sh`
4. Script otomatis update `APP_URL` di `Backend/.env` ke URL ngrok
5. Snippet untuk Roblox executor:

```lua
loadstring(game:HttpGet("https://XXXX.ngrok-free.app/Loader.lua"))()
```

Dashboard ngrok: http://localhost:4040

---

## Production

Tidak ada `start-prod.sh` — jalankan manual:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

Perbedaan production:

- MySQL **tidak** exposed ke host
- `APP_DEBUG=false`, `restart: always`
- Migrasi **tidak** otomatis — jalankan manual:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec backend php artisan migrate --force
```

### Redis (opsional)

```bash
docker compose --profile with-redis up -d
```

---

## Troubleshooting

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| Halaman tanpa CSS via ngrok | Asset Vite tidak ada di `Backend/public/build/` | Jalankan ulang `./start.sh` atau `./setup.sh` |
| Halaman tanpa CSS (localhost:5173) | Mode dev Vite aktif (`public/hot`) | Hapus `Backend/public/hot`, gunakan `./start.sh` |
| Bot Discord: **Gagal HTTP 404** | `LARAVEL_API_URL` mengarah ke ngrok mati | Set `LARAVEL_API_URL=http://nginx` di `Bot_Server/.env`, restart bot |
| Bot: token tidak valid | Token tidak sinkron | Pastikan `DISCORD_BOT_API_TOKEN` = `LARAVEL_API_TOKEN` |
| 502 Bad Gateway | Backend belum siap | `docker compose logs backend`, tunggu healthcheck MySQL |
| `/build/manifest.json` 404 | Asset belum disalin | `./start.sh` (auto-copy dari image) |
| DB connection refused | MySQL masih starting | Tunggu atau cek `docker compose logs mysql` |

### Verifikasi Bot ↔ Backend

```bash
# Dari host
curl -H "Authorization: Bearer TOKEN_ANDA" http://localhost:8000/api/bot/health

# Dari container bot
docker compose exec bot wget -qO- --header="Authorization: Bearer TOKEN_ANDA" http://nginx/api/bot/health
```

Respon sukses: `{"status":true,...}`

### Verifikasi Asset CSS

```bash
curl -I http://localhost:8000/build/manifest.json
# Harus HTTP 200
```

---

## Referensi File

| File | Fungsi |
|------|--------|
| `docker-compose.yml` | Definisi service base |
| `docker-compose.local.yml` | Override dev + ngrok |
| `docker-compose.prod.yml` | Override production |
| `setup.sh` | Setup pertama kali |
| `start.sh` | Start stack harian |
| `stop.sh` | Stop semua container |
| `docker/scripts/common.sh` | Helper compose & env sync |
| `docker/scripts/update-env-url.sh` | Sync URL ngrok → APP_URL |
| `docker/nginx/conf.d/default.conf` | Konfigurasi nginx |
| `docker/backend/Dockerfile` | Image Laravel (build Vite + Composer) |
| `docker/bot/Dockerfile` | Image Discord bot |

---

## Keamanan

- Jangan commit file `.env`, `Backend/.env`, `Bot_Server/.env`
- Gunakan password kuat untuk MySQL di production
- ngrok mengekspos seluruh aplikasi ke internet — gunakan hanya untuk development/staging
- `DISCORD_BOT_API_TOKEN` harus random dan kuat
