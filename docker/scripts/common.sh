#!/usr/bin/env bash

# Shared helpers for setup.sh / start.sh

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE_FILE="${ROOT_DIR}/docker-compose.yml"
COMPOSE_LOCAL_FILE="${ROOT_DIR}/docker-compose.local.yml"
BACKEND_ENV="${ROOT_DIR}/Backend/.env"
BOT_ENV="${ROOT_DIR}/Bot_Server/.env"
DOCKER_ENV="${ROOT_DIR}/.env"

compose() {
    docker compose \
        --env-file "${DOCKER_ENV}" \
        -f "${COMPOSE_FILE}" \
        -f "${COMPOSE_LOCAL_FILE}" \
        "$@"
}

log_info() {
    echo -e "\033[36m[INFO]\033[0m $*" >&2
}

log_ok() {
    echo -e "\033[32m[OK]\033[0m $*" >&2
}

log_warn() {
    echo -e "\033[33m[WARN]\033[0m $*" >&2
}

log_error() {
    echo -e "\033[31m[ERROR]\033[0m $*" >&2
}

require_command() {
    local cmd="$1"
    if ! command -v "$cmd" >/dev/null 2>&1; then
        log_error "Command '${cmd}' tidak ditemukan. Install terlebih dahulu."
        exit 1
    fi
}

check_prerequisites() {
    require_command docker
    if ! docker compose version >/dev/null 2>&1; then
        log_error "Docker Compose plugin tidak ditemukan. Install Docker Desktop atau docker-compose-plugin."
        exit 1
    fi
}

ensure_docker_env() {
    if [ ! -f "${DOCKER_ENV}" ]; then
        cp "${ROOT_DIR}/.env.docker.example" "${DOCKER_ENV}"
        log_ok "File .env docker dibuat dari .env.docker.example"
    fi
}

ensure_app_env_files() {
    if [ ! -f "${BACKEND_ENV}" ]; then
        cp "${ROOT_DIR}/Backend/.env.example" "${BACKEND_ENV}"
        log_ok "Backend/.env dibuat dari .env.example"
    fi

    if [ ! -f "${BOT_ENV}" ]; then
        cp "${ROOT_DIR}/Bot_Server/.env.example" "${BOT_ENV}"
        log_ok "Bot_Server/.env dibuat dari .env.example"
    fi
}

load_docker_env() {
    set -a
    # shellcheck disable=SC1090
    source "${DOCKER_ENV}"
    set +a
}

set_env_var() {
    local file="$1"
    local key="$2"
    local value="$3"

    if grep -q "^${key}=" "${file}"; then
        if [[ "$OSTYPE" == "darwin"* ]]; then
            sed -i '' "s|^${key}=.*|${key}=${value}|" "${file}"
        else
            sed -i "s|^${key}=.*|${key}=${value}|" "${file}"
        fi
    else
        echo "${key}=${value}" >> "${file}"
    fi
}

get_env_var() {
    local file="$1"
    local key="$2"
    grep "^${key}=" "${file}" 2>/dev/null | head -n1 | cut -d'=' -f2- || true
}

sync_database_env() {
    load_docker_env

    set_env_var "${BACKEND_ENV}" "DB_CONNECTION" "mysql"
    set_env_var "${BACKEND_ENV}" "DB_HOST" "mysql"
    set_env_var "${BACKEND_ENV}" "DB_PORT" "3306"
    set_env_var "${BACKEND_ENV}" "DB_DATABASE" "${MYSQL_DATABASE}"
    set_env_var "${BACKEND_ENV}" "DB_USERNAME" "${MYSQL_USER}"
    set_env_var "${BACKEND_ENV}" "DB_PASSWORD" "${MYSQL_PASSWORD}"
}

sync_bot_token() {
    load_docker_env

    if [ -n "${DISCORD_BOT_API_TOKEN:-}" ] && [ "${DISCORD_BOT_API_TOKEN}" != "change_me_to_random_secret_token" ]; then
        set_env_var "${BACKEND_ENV}" "DISCORD_BOT_API_TOKEN" "${DISCORD_BOT_API_TOKEN}"
        set_env_var "${BOT_ENV}" "LARAVEL_API_TOKEN" "${DISCORD_BOT_API_TOKEN}"
    fi
}

generate_random_token() {
    if command -v openssl >/dev/null 2>&1; then
        openssl rand -hex 32
    else
        head -c 32 /dev/urandom | od -An -tx1 | tr -d ' \n'
    fi
}

ensure_discord_bot_token() {
    load_docker_env

    if [ -z "${DISCORD_BOT_API_TOKEN:-}" ] || [ "${DISCORD_BOT_API_TOKEN}" = "change_me_to_random_secret_token" ]; then
        local token
        token="$(generate_random_token)"
        set_env_var "${DOCKER_ENV}" "DISCORD_BOT_API_TOKEN" "${token}"
        log_ok "DISCORD_BOT_API_TOKEN dibuat otomatis"
    fi

    sync_bot_token
}

wait_for_mysql() {
    local retries="${1:-40}"
    local delay="${2:-3}"

    log_info "Menunggu MySQL siap..."

    for ((i = 1; i <= retries; i++)); do
        if compose exec -T mysql mysqladmin ping -h localhost -u root -p"${MYSQL_ROOT_PASSWORD}" --silent >/dev/null 2>&1; then
            log_ok "MySQL siap"
            return 0
        fi
        sleep "${delay}"
    done

    log_error "MySQL tidak siap setelah ${retries} percobaan"
    exit 1
}

wait_for_ngrok_url() {
    local api_url="${1:-http://localhost:4040/api/tunnels}"
    local retries="${2:-30}"
    local delay="${3:-2}"

    log_info "Menunggu tunnel ngrok aktif..."

    for ((i = 1; i <= retries; i++)); do
        local response
        response="$(curl -fsS "${api_url}" 2>/dev/null || true)"

        if [ -n "${response}" ]; then
            local ngrok_url
            ngrok_url="$(echo "${response}" | grep -o '"public_url":"https://[^"]*"' | head -n1 | cut -d'"' -f4 || true)"
            if [ -n "${ngrok_url}" ]; then
                echo "${ngrok_url}"
                return 0
            fi
        fi

        sleep "${delay}"
    done

    return 1
}

clear_laravel_cache() {
    compose exec -T backend php artisan config:clear --no-interaction >/dev/null 2>&1 || true
    compose exec -T backend php artisan view:clear --no-interaction >/dev/null 2>&1 || true
    compose exec -T backend php artisan route:clear --no-interaction >/dev/null 2>&1 || true
}
