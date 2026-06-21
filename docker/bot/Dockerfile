# syntax=docker/dockerfile:1

FROM node:20-alpine AS base

WORKDIR /app

RUN apk add --no-cache bash curl dos2unix ffmpeg python3 make g++ \
    && addgroup -S bot \
    && adduser -S bot -G bot

COPY Bot_Server/package.json Bot_Server/package-lock.json* ./
RUN CFLAGS="-Wno-incompatible-pointer-types -Wno-error=incompatible-pointer-types" \
    CXXFLAGS="-Wno-incompatible-pointer-types -Wno-error=incompatible-pointer-types" \
    npm ci --omit=dev

COPY Bot_Server/ ./

COPY docker/bot/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN dos2unix /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R bot:bot /app

USER bot

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["node", "bot.js"]
