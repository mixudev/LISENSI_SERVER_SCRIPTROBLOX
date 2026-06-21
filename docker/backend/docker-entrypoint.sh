#!/bin/sh
set -e

cd /var/www/html

fix_permissions() {
    if [ -d storage ] && [ -d bootstrap/cache ]; then
        chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
        chmod -R 775 storage bootstrap/cache 2>/dev/null || true
        if [ -d storage/bot_data ]; then
            chmod -R 777 storage/bot_data 2>/dev/null || true
        fi
    fi
}

wait_for_database() {
    host="${DB_HOST:-mysql}"
    port="${DB_PORT:-3306}"
    retries="${DB_WAIT_RETRIES:-30}"
    delay="${DB_WAIT_DELAY:-2}"

    echo "Waiting for database at ${host}:${port}..."

    attempt=1
    while [ "$attempt" -le "$retries" ]; do
        if php -r "
            \$dsn = sprintf('mysql:host=%s;port=%s', getenv('DB_HOST') ?: 'mysql', getenv('DB_PORT') ?: '3306');
            try {
                new PDO(\$dsn, getenv('DB_USERNAME') ?: 'root', getenv('DB_PASSWORD') ?: '', [PDO::ATTR_TIMEOUT => 2]);
                exit(0);
            } catch (Throwable \$e) {
                exit(1);
            }
        "; then
            echo "Database is ready."
            return 0
        fi

        echo "Database not ready (attempt ${attempt}/${retries})..."
        attempt=$((attempt + 1))
        sleep "$delay"
    done

    echo "Database connection timed out."
    exit 1
}

run_migrations() {
    if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
        echo "Running database migrations..."
        php artisan migrate --force --no-interaction
    fi
}

sync_build_assets() {
    if [ -d /var/www/html/public/build-image ]; then
        echo "Syncing frontend build assets..."
        rm -rf /var/www/html/public/build/*
        cp -r /var/www/html/public/build-image/. /var/www/html/public/build/
    fi
}

if [ "$(id -u)" = "0" ]; then
    fix_permissions
    sync_build_assets
fi

if [ "${WAIT_FOR_DB:-true}" = "true" ]; then
    wait_for_database
fi

run_migrations

if [ "$(id -u)" = "0" ]; then
    exec su-exec www-data "$@"
fi

exec "$@"
