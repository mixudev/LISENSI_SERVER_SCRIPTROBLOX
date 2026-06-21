# syntax=docker/dockerfile:1

# ─── Stage 1: Build frontend assets (Vite + Tailwind) ───────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app

COPY Backend/package.json Backend/package-lock.json ./
RUN npm ci --ignore-scripts

COPY Backend/resources ./resources
COPY Backend/public ./public
COPY Backend/vite.config.js ./
RUN npm run build

# ─── Stage 2: PHP dependencies ───────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY Backend/composer.json Backend/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ─── Stage 3: Production PHP-FPM image ───────────────────────────────────────
FROM php:8.4-fpm-alpine AS production

RUN apk add --no-cache \
    bash \
    curl \
    dos2unix \
    netcat-openbsd \
    su-exec \
    freetype-dev \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && rm -rf /var/cache/apk/*

COPY docker/backend/php.ini /usr/local/etc/php/conf.d/99-laravel.ini
COPY docker/backend/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/backend/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN dos2unix /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

COPY --chown=www-data:www-data Backend/ ./
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build-image

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]

# ─── Stage 4: Development image (includes dev dependencies) ──────────────────
FROM production AS development

USER root

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Development tidak mengubah user — entrypoint handle permission & su-exec
