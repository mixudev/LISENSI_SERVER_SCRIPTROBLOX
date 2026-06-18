# License Dashboard Bot

Bot Discord "Dashboard GUI Statis Satu Klik" untuk sistem manajemen lisensi software, terintegrasi dengan Web API Laravel. Dibangun dengan **discord.js v14** + **Axios**.

## ✨ Fitur

- Dashboard berbentuk Embed + 4 tombol persistent (tidak dobel, otomatis di-edit jika kode berubah).
- Semua respon ke user bersifat privat (`ephemeral: true`).
- 🔑 **Generate Key** — khusus role Admin/Reseller, via Modal input durasi hari.
- 🔄 **Reset HWID** — reset HWID lisensi milik user yang klik.
- 📜 **Get Script** — kirim script loader langsung tanpa hit API.
- 📊 **Get Stats** — tampilkan detail status lisensi user.
- Error handling ketat: jika Laravel API down/timeout, bot tidak crash dan memberi pesan ramah.

## 📁 Struktur Proyek

```
license-dashboard-bot/
├── bot.js                          # Entry point utama
├── package.json
├── .env.example
├── config/
│   └── index.js                    # Load & validasi environment variables
├── services/
│   └── laravelService.js           # Semua HTTP request ke Laravel API (Axios)
├── dashboard/
│   └── panel.js                    # Embed & ActionRow tombol dashboard
├── interactions/
│   ├── buttons/
│   │   ├── getStats.js
│   │   ├── resetHwid.js
│   │   ├── getScript.js
│   │   └── generateKey.js          # Cek role admin + trigger modal
│   └── modals/
│       └── generateKeyModal.js     # Handle submit modal generate key
└── utils/
    └── replyHelper.js              # Embed standar sukses/error/forbidden
```

## 🚀 Instalasi

1. **Install dependencies**
   ```bash
   npm install
   ```

2. **Setup environment variables**

   Copy `.env.example` menjadi `.env`, lalu isi sesuai data Anda:
   ```bash
   cp .env.example .env
   ```

   | Variable | Keterangan |
   |---|---|
   | `DISCORD_TOKEN` | Token bot dari Discord Developer Portal |
   | `CLIENT_ID` | Application Client ID bot |
   | `DASHBOARD_CHANNEL_ID` | ID channel tempat dashboard ditampilkan |
   | `ADMIN_ROLE_ID` | ID role yang boleh generate key |
   | `LARAVEL_API_URL` | Base URL Web API Laravel Anda (tanpa trailing slash) |
   | `LARAVEL_API_TOKEN` | Bearer token untuk autentikasi ke Laravel |
   | `LARAVEL_API_TIMEOUT` | (opsional) timeout request dalam ms, default 8000 |

3. **Permission & Intents Bot di Discord Developer Portal**

   Bot tidak memerlukan privileged intent apa pun (tidak pakai Message Content Intent karena tidak membaca isi pesan user). Pastikan bot memiliki permission di channel dashboard:
   - `View Channel`
   - `Send Messages`
   - `Embed Links`
   - `Read Message History` (untuk cek pesan lama agar tidak dobel)

4. **Jalankan bot**
   ```bash
   npm start
   ```

   Untuk mode development dengan auto-restart:
   ```bash
   npm run dev
   ```

## 🔌 Kontrak API Laravel yang Diharapkan

Bot ini mengasumsikan endpoint berikut tersedia di Laravel (sesuaikan path/response di `services/laravelService.js` jika berbeda):

### GET `/api/license/stats?discord_id=...`
```json
{
  "message": "OK",
  "data": {
    "key": "XXXX-XXXX-XXXX",
    "status": "active",
    "hwid": "ABC123...",
    "expires_at": "2026-12-31"
  }
}
```

### POST `/api/license/reset-hwid`
Body: `{ "discord_id": "..." }`
```json
{ "message": "HWID berhasil direset." }
```

### POST `/api/license/generate`
Body: `{ "discord_id": "...", "duration_days": 30 }`
```json
{
  "message": "Key berhasil dibuat",
  "data": { "key": "NEW-KEY-XXXX" }
}
```

Semua request membawa header:
```
Authorization: Bearer <LARAVEL_API_TOKEN>
```

## 🧩 Menambah Tombol Baru

1. Buat file handler baru di `interactions/buttons/namaTombol.js`.
2. Tambahkan tombolnya di `dashboard/panel.js` (di `BUTTON_IDS` dan `buildDashboardButtons`).
3. Daftarkan mapping `customId -> handler` di `bot.js` pada object `buttonHandlers`.

Tidak perlu register Slash Command apa pun — arsitektur ini murni berbasis Button & Modal interaction.
