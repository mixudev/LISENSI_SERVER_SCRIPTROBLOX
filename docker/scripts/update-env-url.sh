#!/usr/bin/env bash

# Update APP_URL (Backend) dengan static ngrok URL.
# URL tidak berubah-ubah karena menggunakan custom domain ngrok:
#   ngrok http --url=unfertile-proconsularly-dorris.ngrok-free.dev 8000
# LARAVEL_API_URL bot tetap http://nginx (internal Docker).

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=common.sh
source "${SCRIPT_DIR}/common.sh"

# Static ngrok URL — tidak berubah, tidak perlu polling API
STATIC_NGROK_URL="https://unfertile-proconsularly-dorris.ngrok-free.dev"

ngrok_url="${STATIC_NGROK_URL}"

log_ok "URL ngrok (static): ${ngrok_url}"

set_env_var "${BACKEND_ENV}" "APP_URL" "${ngrok_url}"
# Bot di dalam Docker network — gunakan nginx internal, bukan URL ngrok publik
set_env_var "${BOT_ENV}" "LARAVEL_API_URL" "http://nginx"

# Update Midtrans notification URL agar pakai ngrok URL yang sama
set_env_var "${BACKEND_ENV}" "MIDTRANS_NOTIFICATION_URL" "${ngrok_url}/api/midtrans/callback"

log_ok "Backend/.env → APP_URL=${ngrok_url}"
log_ok "Backend/.env → MIDTRANS_NOTIFICATION_URL=${ngrok_url}/api/midtrans/callback"
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

compose restart backend queue bot > /dev/null 2>&1 || true

echo ""
log_info "Roblox executor:"
echo "  loadstring(game:HttpGet(\"${ngrok_url}/Loader.lua\"))()"
echo ""
log_info "Dashboard admin: ${ngrok_url}/admin/dashboard"
log_info "Test inject:     ${ngrok_url}/admin/inject-test"
log_info "Midtrans webhook: ${ngrok_url}/api/midtrans/callback"
