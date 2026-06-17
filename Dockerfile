# syntax=docker/dockerfile:1
#
# Multi-stage build for kleverkat-web (Laravel 13 + Filament + FrankenPHP).
# Stages:
#   base     → FrankenPHP runtime + PHP extensions + composer binary (shared)
#   vendor   → PHP dependencies (prod-only) + optimized autoloader
#   frontend → compiled Vite/Tailwind assets (needs vendor/ for Flux & Filament CSS)
#   dev      → lightweight dev runtime (source is bind-mounted via docker-compose)
#   prod     → final, optimized, non-root runtime image
#
# Full explanation: see DOCKER.md.

# ---------------------------------------------------------------------------
# base — common foundation for every PHP stage
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4-bookworm AS base

WORKDIR /app

# curl is needed by the production HEALTHCHECK; the rest are runtime PHP extensions.
RUN apt-get update \
 && apt-get install -y --no-install-recommends curl \
 && rm -rf /var/lib/apt/lists/* \
 && install-php-extensions \
        pdo_mysql \
        redis \
        intl \
        gd \
        zip \
        bcmath \
        pcntl \
        opcache \
        exif

# Composer binary (copied from the official image — no global install needed).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# vendor — install PHP dependencies and build the optimized autoloader
# ---------------------------------------------------------------------------
FROM base AS vendor

# 1) Install deps WITHOUT the autoloader/scripts first → this layer is cached and
#    only rebuilds when composer.json / composer.lock change.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-autoloader

# 2) Bring in the source, THEN build the optimized classmap (so app/ classes are
#    included) and discover packages. APP_KEY is a throwaway value passed *inline*
#    (never persisted as ENV → no secret baked into any layer, no build warning).
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts \
 && APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= php artisan package:discover --ansi

# ---------------------------------------------------------------------------
# frontend — compile assets with Vite + Tailwind v4
# (depends on vendor/: app.css imports vendor/livewire/flux, the Filament theme
#  imports vendor/filament/... — so vendor/ MUST be present at build time)
# ---------------------------------------------------------------------------
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

# Reproducible install from the lockfile (cached unless package*.json changes).
COPY package.json package-lock.json ./
RUN npm ci

# Only what the build needs: config, source assets, and the PHP vendor CSS sources.
COPY vite.config.js ./
COPY resources ./resources
COPY --from=vendor /app/vendor ./vendor

RUN npm run build

# ---------------------------------------------------------------------------
# dev — development runtime (source bind-mounted at runtime via compose)
# ---------------------------------------------------------------------------
FROM base AS dev

# No app source is copied here on purpose: docker-compose mounts the host project
# into /app, so you edit on the host and the container sees changes instantly.
COPY docker/php/php.dev.ini  $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/Caddyfile        /etc/frankenphp/Caddyfile

EXPOSE 80
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]

# ---------------------------------------------------------------------------
# prod — final optimized image (no Composer deps source, no Node, non-root)
# ---------------------------------------------------------------------------
FROM base AS prod

ENV APP_ENV=production \
    APP_DEBUG=false

# Application source + built artifacts from the previous stages.
COPY . .
COPY --from=vendor   /app/vendor       ./vendor
COPY --from=frontend /app/public/build ./public/build

# Runtime configuration.
COPY docker/php/php.prod.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/Caddyfile        /etc/frankenphp/Caddyfile
COPY docker/entrypoint.sh    /usr/local/bin/entrypoint

# Publish Filament's core JS/CSS into public/, drop the build-only composer binary,
# make scripts executable, and hand the writable paths to the unprivileged runtime
# user (www-data ships with the image).
RUN chmod +x /usr/local/bin/entrypoint \
 && APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= php artisan filament:assets \
 && rm -f /usr/bin/composer \
 && mkdir -p /data/caddy /config/caddy \
 && chown -R www-data:www-data storage bootstrap/cache public /data/caddy /config/caddy

# Drop root: FrankenPHP can still bind :80/:443 thanks to the setcap'd binary.
USER www-data

EXPOSE 80 443

HEALTHCHECK --interval=15s --timeout=5s --start-period=30s --retries=5 \
    CMD curl -fsS http://localhost/up || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
