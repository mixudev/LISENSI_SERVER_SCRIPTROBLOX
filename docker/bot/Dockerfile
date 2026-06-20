# syntax=docker/dockerfile:1

FROM node:20-alpine AS base

WORKDIR /app

RUN apk add --no-cache bash curl dos2unix \
    && addgroup -S bot \
    && adduser -S bot -G bot

COPY Bot_Server/package.json Bot_Server/package-lock.json* ./
RUN npm ci --omit=dev --ignore-scripts

COPY Bot_Server/ ./

COPY docker/bot/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN dos2unix /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R bot:bot /app

USER bot

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["node", "bot.js"]
