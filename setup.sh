#!/usr/bin/env bash

# =============================================================================
# setup.sh — First-time Docker setup
# Jalankan sekali: chmod +x setup.sh && ./setup.sh
# =============================================================================

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=docker/scripts/common.sh
source "${ROOT_DIR}/docker/scripts/common.sh"

main() {
    echo ""
    echo "========================================"
    echo "  SCRIPT_LISENSI — Docker Setup"
    echo "========================================"
    echo ""

    check_prerequisites
    ensure_docker_env
    ensure_app_env_files
    ensure_discord_bot_token
    sync_database_env

    # Default URL sebelum ngrok
    set_env_var "${BACKEND_ENV}" "APP_URL" "http://localhost:${APP_PORT:-8000}"
    set_env_var "${BOT_ENV}" "LARAVEL_API_URL" "http://nginx"

    log_info "Membangun image Docker (pertama kali bisa memakan waktu beberapa menit)..."
    compose build

    log_info "Menyalin asset frontend dari image backend ke host..."
    sync_public_assets

    log_info "Menjalankan MySQL..."
    compose up -d mysql
    wait_for_mysql

    log_info "Generate APP_KEY jika belum ada..."
    local app_key
    app_key="$(get_env_var "${BACKEND_ENV}" "APP_KEY")"
    if [ -z "${app_key}" ]; then
        compose run --rm --no-deps backend php artisan key:generate --force --no-interaction
        log_ok "APP_KEY dibuat"
    else
        log_ok "APP_KEY sudah ada"
    fi

    log_info "Menjalankan migrasi database..."
    compose run --rm --no-deps \
        -e WAIT_FOR_DB=true \
        -e RUN_MIGRATIONS=true \
        backend php artisan migrate --force --no-interaction

    log_info "Menjalankan seeder (opsional)..."
    compose run --rm --no-deps backend php artisan db:seed --force --no-interaction 2>/dev/null || \
        log_warn "Seeder dilewati atau gagal (tidak kritis)"

    echo ""
    log_ok "Setup selesai!"
    echo ""
    echo "Langkah berikutnya:"
    echo "  1. Isi Discord credentials di Bot_Server/.env"
    echo "     - DISCORD_TOKEN, CLIENT_ID, DASHBOARD_CHANNEL_ID, ADMIN_ROLE_ID"
    echo "  2. Isi NGROK_AUTHTOKEN di .env (root) — https://dashboard.ngrok.com"
    echo "  3. Jalankan: ./start.sh"
    echo ""
}

sync_public_assets() {
    mkdir -p "${ROOT_DIR}/Backend/public/build"

    compose run --rm --no-deps \
        -v "${ROOT_DIR}/Backend/public:/host-public" \
        backend sh -c 'if [ -d /var/www/html/public/build ]; then cp -rf /var/www/html/public/build/. /host-public/build/; fi'

    if [ -f "${ROOT_DIR}/Backend/public/build/manifest.json" ]; then
        log_ok "Asset Vite tersedia di Backend/public/build"
    else
        log_warn "manifest.json tidak ditemukan — jalankan ulang setup atau npm run build di Backend/"
    fi
}

main "$@"
