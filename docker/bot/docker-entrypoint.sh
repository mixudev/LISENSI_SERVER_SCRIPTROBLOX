#!/bin/sh
set -e

wait_for_laravel() {
    api_url="${LARAVEL_API_URL:-http://nginx}"
    health_url="${api_url%/}/up"
    retries="${LARAVEL_WAIT_RETRIES:-30}"
    delay="${LARAVEL_WAIT_DELAY:-2}"

    echo "Waiting for Laravel API at ${health_url}..."

    attempt=1
    while [ "$attempt" -le "$retries" ]; do
        if curl -fsS -o /dev/null "${health_url}"; then
            echo "Laravel API is ready."
            return 0
        fi

        echo "Laravel API not ready (attempt ${attempt}/${retries})..."
        attempt=$((attempt + 1))
        sleep "$delay"
    done

    echo "Warning: Laravel API not reachable yet. Bot will start anyway."
}

if [ "${WAIT_FOR_LARAVEL:-true}" = "true" ]; then
    wait_for_laravel
fi

exec "$@"
