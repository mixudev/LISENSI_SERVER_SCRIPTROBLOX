#!/usr/bin/env bash

# =============================================================================
# stop.sh — Stop semua container Docker
# =============================================================================

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=docker/scripts/common.sh
source "${ROOT_DIR}/docker/scripts/common.sh"

log_info "Menghentikan semua container..."
compose --profile ngrok down

log_ok "Semua container dihentikan."
