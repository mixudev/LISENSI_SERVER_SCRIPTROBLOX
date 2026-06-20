#!/usr/bin/env bash

# Update APP_URL (Backend) dari tunnel ngrok aktif.
# LARAVEL_API_URL bot tetap http://nginx (internal Docker).

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=common.sh
source "${SCRIPT_DIR}/common.sh"

NGROK_API_URL="${NGROK_API_URL:-http://localhost:${NGROK_UI_PORT:-4040}/api/tunnels}"
FALLBACK_URL="${1:-}"

ngrok_url="${FALLBACK_URL}"

if [ -z "${ngrok_url}" ]; then
    ngrok_url="$(wait_for_ngrok_url "${NGROK_API_URL}" 30 2 || true)"
fi

if [ -z "${ngrok_url}" ]; then
    log_warn "Tunnel ngrok tidak ditemukan. Lewati update URL."
    log_warn "Set manual: APP_URL di Backend/.env dan LARAVEL_API_URL di Bot_Server/.env"
    exit 0
fi

log_ok "URL ngrok: ${ngrok_url}"

set_env_var "${BACKEND_ENV}" "APP_URL" "${ngrok_url}"
# Bot di dalam Docker network — gunakan nginx internal, bukan URL ngrok publik
set_env_var "${BOT_ENV}" "LARAVEL_API_URL" "http://nginx"

log_ok "Backend/.env → APP_URL=${ngrok_url}"
log_ok "Bot_Server/.env → LARAVEL_API_URL=http://nginx (internal Docker)"

# Hapus dev hot file agar @vite tidak mengarah ke localhost:5173
if [ -f "${ROOT_DIR}/Backend/public/hot" ]; then
    rm -f "${ROOT_DIR}/Backend/public/hot"
    log_ok "Backend/public/hot dihapus (mode dev Vite dinonaktifkan)"
fi

# Pastikan asset build ada di host untuk nginx
if [ ! -f "${ROOT_DIR}/Backend/public/build/manifest.json" ]; then
    log_warn "Asset build belum ada, menyalin dari image..."
    sync_public_assets
fi

clear_laravel_cache

compose restart backend queue bot >/dev/null 2>&1 || true

echo ""
log_info "Roblox executor:"
echo "  loadstring(game:HttpGet(\"${ngrok_url}/Loader.lua\"))()"
echo ""
log_info "Dashboard admin: ${ngrok_url}/admin/dashboard"
log_info "Test inject:     ${ngrok_url}/admin/inject-test"
