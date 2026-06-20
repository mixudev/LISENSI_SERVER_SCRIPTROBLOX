# Contributing to LimeHub License Server

Terima kasih atas minatnya untuk berkontribusi! Dokumen ini menjelaskan cara berkontribusi ke project ini, baik untuk Backend (Laravel), Discord Bot, maupun infrastruktur Docker.

## Daftar Isi

- [Kode Etik](#kode-etik)
- [Cara Memulai](#cara-memulai)
- [Struktur Project](#struktur-project)
- [Alur Kontribusi](#alur-kontribusi)
- [Standar Kode](#standar-kode)
- [Menulis Test](#menulis-test)
- [Commit Message](#commit-message)
- [Pull Request](#pull-request)
- [Melaporkan Bug](#melaporkan-bug)
- [Mengusulkan Fitur](#mengusulkan-fitur)

---

## Kode Etik

Project ini mengikuti [Code of Conduct](./CODE_OF_CONDUCT.md). Dengan berpartisipasi, kamu diharapkan menjunjung tinggi aturan tersebut.

---

## Cara Memulai

1. **Fork** repository ini
2. **Clone** fork kamu:
   ```bash
   git clone https://github.com/USERNAME-KAMU/script-lisensi.git
   cd SCRIPT_LISENSI
   ```
3. **Setup environment** — pilih salah satu:

   **Via Docker (disarankan):**
   ```bash
   chmod +x setup.sh start.sh stop.sh
   ./setup.sh
   ./start.sh
   ```

   **Manual:**
   ```bash
   cd Backend
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   npm run dev
   ```

4. **Buat branch baru** untuk perubahanmu:
   ```bash
   git checkout -b fitur/nama-fitur
   # atau
   git checkout -b fix/nama-bug
   ```

---

## Struktur Project

```
SCRIPT_LISENSI/
├── Backend/              # Laravel 13 — API lisensi, dashboard admin/user
├── Bot_Server/           # Discord bot (discord.js v14)
├── docker/               # Dockerfile, nginx conf, entrypoint scripts
├── docker-compose.yml    # Definisi service base
├── setup.sh / start.sh   # Script otomasi Docker
└── DOCKER.md             # Panduan Docker lengkap
```

Sebelum mengubah sesuatu, pastikan paham di layer mana perubahan itu seharusnya berada:

- **Logika bisnis lisensi/HWID/produk** → `Backend/app/Services/`
- **Endpoint API** → `Backend/app/Http/Controllers/Api/`
- **Dashboard admin/user** → `Backend/app/Http/Controllers/Admin|User/`
- **Perintah/tombol Discord** → `Bot_Server/interactions/`
- **Infrastruktur/deployment** → `docker/`

---

## Alur Kontribusi

1. Pastikan branch kamu up-to-date dengan `main`:
   ```bash
   git fetch origin
   git rebase origin/main
   ```
2. Lakukan perubahan, commit secara logis dan kecil-kecil (lihat [Commit Message](#commit-message))
3. Jalankan test dan linter sebelum push (lihat [Standar Kode](#standar-kode))
4. Push ke fork kamu dan buka Pull Request ke branch `main`

---

## Standar Kode

### Backend (PHP/Laravel)

- Format kode dengan **Laravel Pint** sebelum commit:
  ```bash
  cd Backend
  vendor/bin/pint --dirty
  ```
- Ikuti konvensi PSR-12 dan struktur layer yang sudah ada (`Service` untuk logika bisnis, `Repository` untuk query/cache, `Controller` tetap tipis)
- Jangan taruh logika bisnis langsung di Controller — delegasikan ke `Service`

### Discord Bot (Node.js)

- Ikuti struktur folder yang sudah ada: `interactions/buttons/`, `interactions/modals/`, `services/`
- Gunakan `async/await`, hindari callback bersarang
- Pastikan setiap handler punya error handling agar bot tidak crash saat Laravel API down/timeout

### Shell Script / Docker

- **Wajib gunakan LF line ending**, bukan CRLF — project ini sudah punya `.gitattributes` untuk menormalisasi ini otomatis, tapi pastikan editor kamu (terutama di Windows) tidak override setting tersebut
- Test perubahan Dockerfile dengan build ulang dari nol:
  ```bash
  docker compose build --no-cache <service>
  ```

---

## Menulis Test

Project backend menggunakan **Pest 4**. Setiap fitur baru atau bug fix pada logika bisnis **wajib** disertai test.

```bash
cd Backend

# Jalankan semua test
php artisan test

# Test spesifik
php artisan test --filter=NamaTest

# Mode ringkas
php artisan test --compact
```

Tempatkan test sesuai domain:
- Aktivasi/validasi lisensi → `tests/Feature/LicenseActivateTest.php` (atau sejenis)
- Akses script user vs admin → `tests/Feature/ScriptAccessTest.php`
- Keamanan token modul → `tests/Feature/ModuleSecurityTest.php`

Untuk Discord bot, belum ada test suite otomatis — kontribusi setup test (Jest/Vitest) sangat diterima.

---

## Commit Message

Gunakan format singkat dan deskriptif, idealnya mengikuti pola [Conventional Commits](https://www.conventionalcommits.org/):

```
<tipe>(<scope>): <deskripsi singkat>

[opsional: penjelasan lebih detail]
```

**Tipe yang umum dipakai:**

| Tipe | Kegunaan |
|------|----------|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `docs` | Perubahan dokumentasi saja |
| `refactor` | Perubahan kode tanpa mengubah behavior |
| `test` | Menambah/memperbaiki test |
| `chore` | Perubahan tooling, dependency, config |
| `docker` | Perubahan infrastruktur Docker |

**Contoh:**
```
fix(backend): perbaiki error_log PHP-FPM yang gagal akses /proc/self/fd/2
feat(bot): tambah tombol get-stats dengan ephemeral reply
docs(readme): tambah diagram arsitektur sistem
```

---

## Pull Request

Sebelum membuka PR, pastikan:

- [ ] Branch sudah di-rebase dengan `main` terbaru
- [ ] Test lokal lulus (`php artisan test`)
- [ ] Kode sudah diformat (`vendor/bin/pint --dirty`)
- [ ] Tidak ada file `.env`, kredensial, atau token yang ikut ter-commit
- [ ] Deskripsi PR menjelaskan **apa** yang diubah dan **mengapa**
- [ ] Jika mengubah behavior API, update juga dokumentasi terkait di `README.md`

Template deskripsi PR yang disarankan:

```markdown
## Ringkasan
Penjelasan singkat perubahan ini.

## Jenis perubahan
- [ ] Bug fix
- [ ] Fitur baru
- [ ] Breaking change
- [ ] Dokumentasi

## Cara testing
Langkah untuk reviewer memverifikasi perubahan ini.
```

---

## Melaporkan Bug

Buka [Issue baru](../../issues/new) dengan informasi:

1. **Deskripsi** — apa yang terjadi vs apa yang diharapkan
2. **Langkah reproduksi** — step-by-step agar bug bisa direplikasi
3. **Environment** — OS, versi PHP/Node, dijalankan via Docker atau manual
4. **Log relevan** — potongan log dari `storage/logs/laravel.log`, `docker compose logs`, atau console Discord bot
5. **Screenshot** jika berkaitan dengan tampilan dashboard

> ⚠️ **Jangan** sertakan license key asli, token Discord, `APP_KEY`, atau kredensial lain di issue publik. Mask atau ganti dengan placeholder.

---

## Mengusulkan Fitur

Buka [Issue baru](../../issues/new) dengan label `enhancement`, jelaskan:

- Masalah yang ingin diselesaikan fitur ini
- Usulan implementasi (opsional, boleh diskusi dulu sebelum coding)
- Apakah ini breaking change terhadap API/dashboard yang sudah ada

Untuk perubahan besar (misal menambah tipe lisensi baru, mengubah skema database inti), disarankan diskusi di Issue dulu sebelum membuka PR, supaya arahnya sejalan dengan desain project.

---

Terima kasih sudah membantu mengembangkan project ini! 🙌
