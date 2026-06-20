# Security Policy

Keamanan adalah prioritas utama untuk project ini, mengingat sistem ini menangani **license key**, **token autentikasi API**, dan **distribusi script** ke executor pihak ketiga. Dokumen ini menjelaskan versi yang didukung dan cara melaporkan vulnerability secara bertanggung jawab.

---

## Versi yang Didukung

| Versi | Status Dukungan Keamanan |
|-------|---------------------------|
| `main` (terbaru) | ✅ Didukung penuh |
| Versi rilis sebelumnya | ⚠️ Hanya fix kritikal, case-by-case |

Disarankan selalu menjalankan versi terbaru dari branch `main`, terutama untuk komponen yang menangani autentikasi API (`LicenseController`, `ModuleAccessService`) dan bot Discord.

---

## Melaporkan Vulnerability

**Jangan buka Issue publik untuk melaporkan masalah keamanan.** Issue publik bisa dieksploitasi sebelum patch tersedia.

Sebagai gantinya:

1. Laporkan secara privat melalui **GitHub Security Advisory** (tab *Security* → *Report a vulnerability*) di repository ini, atau
2. Hubungi maintainer langsung melalui kontak yang tercantum di profil GitHub `mixudev`.

### Informasi yang perlu disertakan

- Deskripsi vulnerability dan dampaknya (misal: bypass HWID, akses script tanpa lisensi valid, token leak)
- Langkah reproduksi yang jelas
- Endpoint/file/baris kode yang terdampak (jika diketahui)
- Versi/commit yang digunakan saat menemukan masalah
- (Opsional) Saran perbaikan

### Yang bisa diharapkan setelah melapor

- Konfirmasi penerimaan laporan dalam **3 hari kerja**
- Update status investigasi secara berkala
- Kredit (jika diinginkan) setelah fix dirilis, kecuali kamu memilih anonim

Mohon beri waktu yang wajar untuk perbaikan sebelum mempublikasikan detail vulnerability secara terbuka (*responsible disclosure*).

---

## Area Sensitif dalam Project Ini

Beberapa bagian sistem yang perlu perhatian ekstra saat audit atau kontribusi:

| Area | Risiko |
|------|--------|
| `POST /api/license/activate`, `/check`, `/get` | Bypass validasi HWID/key, brute force key |
| `GET /modules/{token}/{path}` | Path traversal, akses `loader.lua` tanpa lisensi |
| `DISCORD_BOT_API_TOKEN` / `LARAVEL_API_TOKEN` | Jika leak, bot/API bisa diakses pihak tak sah |
| `GITHUB_PAT` | Jika leak, repo script privat bisa diakses |
| Rate limiting (60/min API, 120/min modul) | Bypass via header spoofing atau IP rotation |
| Log API (`api_logs`) | Pastikan key tetap termasking (`LZD-FC8198-****-****-****-16FCA3`), jangan log plaintext |
| File `.env`, `Backend/.env`, `Bot_Server/.env` | **Tidak boleh** pernah masuk ke commit history |

---

## Praktik Keamanan yang Diharapkan dari Kontributor

- Jangan hardcode credential, token, atau key apa pun di kode — selalu lewat environment variable
- Saat menambah endpoint baru, terapkan rate limiting yang konsisten dengan endpoint sejenis
- Validasi dan sanitasi semua input path (terutama untuk endpoint modul Lua) menggunakan `realpath()` atau setara, untuk mencegah path traversal
- Jangan expose stack trace atau detail error internal di response production (`APP_DEBUG=false` wajib di production)
- Saat menulis test untuk fitur sensitif (aktivasi, HWID, token modul), sertakan test case untuk skenario **gagal/disalahgunakan**, bukan hanya happy path

---

## Dependency Security

Project ini menggunakan Composer (PHP) dan npm (Node.js). Disarankan menjalankan audit dependency secara berkala:

```bash
# Backend
cd Backend
composer audit

# Bot
cd Bot_Server
npm audit
```

Jika ditemukan vulnerability pada dependency pihak ketiga, prioritaskan update versi sebelum merilis perubahan lain.

---

Terima kasih telah membantu menjaga keamanan project ini.
