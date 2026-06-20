#!/usr/bin/env bash

# =============================================================================
# start.sh — Jalankan semua service Docker + ngrok + update URL env
# Jalankan: chmod +x start.sh && ./start.sh
# =============================================================================

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=docker/scripts/common.sh
source "${ROOT_DIR}/docker/scripts/common.sh"

main() {
    echo ""
    echo "========================================"
    echo "  SCRIPT_LISENSI — Start Local"
    echo "========================================"
    echo ""

    check_prerequisites

    if [ ! -f "${DOCKER_ENV}" ]; then
        log_error "File .env belum ada. Jalankan ./setup.sh terlebih dahulu."
        exit 1
    fi

    if [ ! -f "${BACKEND_ENV}" ]; then
        log_error "Backend/.env belum ada. Jalankan ./setup.sh terlebih dahulu."
        exit 1
    fi

    load_docker_env
    sync_database_env
    sync_bot_token

    # Hapus Vite dev hot file — ngrok tidak bisa akses localhost:5173
    if [ -f "${ROOT_DIR}/Backend/public/hot" ]; then
        rm -f "${ROOT_DIR}/Backend/public/hot"
        log_ok "Backend/public/hot dihapus"
    fi

    # Pastikan asset frontend ada untuk nginx
    if [ ! -f "${ROOT_DIR}/Backend/public/build/manifest.json" ]; then
        log_warn "Asset build belum ada, menyalin dari image..."
        mkdir -p "${ROOT_DIR}/Backend/public/build"
        compose build backend 2>/dev/null || compose build backend
        compose run --rm --no-deps \
            -v "${ROOT_DIR}/Backend/public:/host-public" \
            backend sh -c 'if [ -d /var/www/html/public/build ]; then cp -rf /var/www/html/public/build/. /host-public/build/; fi'
    fi

    log_info "Menjalankan semua container Docker..."
    compose up -d --build

    wait_for_mysql

    log_info "Menunggu backend siap..."
    sleep 5
    clear_laravel_cache

    # ─── ngrok ───────────────────────────────────────────────────────────────
    if [ -n "${NGROK_AUTHTOKEN:-}" ]; then
        log_info "Menjalankan ngrok tunnel..."
        compose --profile ngrok up -d ngrok

        bash "${ROOT_DIR}/docker/scripts/update-env-url.sh"
        # Pastikan bot tetap pakai URL internal Docker
        set_env_var "${BOT_ENV}" "LARAVEL_API_URL" "http://nginx"
    else
        log_warn "NGROK_AUTHTOKEN kosong di .env — ngrok tidak dijalankan"
        local local_url="http://localhost:${APP_PORT:-8000}"
        set_env_var "${BACKEND_ENV}" "APP_URL" "${local_url}"
        set_env_var "${BOT_ENV}" "LARAVEL_API_URL" "http://nginx"
        compose restart bot >/dev/null 2>&1 || true
        clear_laravel_cache
        log_info "APP_URL diset ke ${local_url} (tanpa ngrok)"
        log_info "Untuk Roblox testing, isi NGROK_AUTHTOKEN di .env lalu jalankan ulang ./start.sh"
    fi

    echo ""
    log_ok "Semua service berjalan!"
    echo ""
    compose ps
    echo ""
    log_info "Perintah berguna:"
    echo "  docker compose logs -f          # Lihat semua log"
    echo "  docker compose logs -f bot      # Log Discord bot"
    echo "  docker compose logs -f backend  # Log Laravel"
    echo "  docker compose down             # Stop semua"
    echo ""
    log_info "Aplikasi lokal: http://localhost:${APP_PORT:-8000}"
    if [ -n "${NGROK_AUTHTOKEN:-}" ]; then
        log_info "ngrok dashboard: http://localhost:${NGROK_UI_PORT:-4040}"
    fi
    echo ""
}

main "$@"
