ARG FRANKENPHP_VERSION=1.10.1
ARG PHP_VERSION=8.5

FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}-bookworm AS php_base

ARG APP_USER=laravel
ARG WWWUSER=1000
ARG WWWGROUP=1000

ENV APP_HOME=/app \
    SERVER_NAME=:80 \
    SERVER_ROOT=/app/public \
    COMPOSER_ALLOW_SUPERUSER=1 \
    XDG_CONFIG_HOME=/tmp

WORKDIR /app

RUN set -eux; \
    groupadd --force -g "${WWWGROUP}" "${APP_USER}"; \
    useradd --uid "${WWWUSER}" --gid "${WWWGROUP}" --create-home --shell /bin/bash "${APP_USER}"; \
    apt-get update; \
    apt-get install -y --no-install-recommends ca-certificates curl; \
    install-php-extensions \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_sqlite \
        redis \
        zip; \
    setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
    rm -rf /var/lib/apt/lists/*

FROM php_base AS php_build

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip; \
    install-php-extensions @composer; \
    rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

COPY . .

RUN composer dump-autoload \
    --classmap-authoritative \
    --no-dev \
    --no-interaction \
 && php artisan package:discover --ansi \
 && php artisan vendor:publish --tag=statamic-cp --force --no-interaction --ansi \
 && php artisan vendor:publish --tag=statamic-frontend --force --no-interaction --ansi

FROM node:22-bookworm-slim AS frontend_build

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./vite.config.js

RUN npm run build

FROM php_base AS prod

ARG APP_USER=laravel
ARG WWWGROUP=1000

ENV APP_ENV=production \
    APP_HOME=/app \
    SERVER_NAME=:80 \
    SERVER_ROOT=/app/public

WORKDIR /app

RUN mv "${PHP_INI_DIR}/php.ini-production" "${PHP_INI_DIR}/php.ini"

COPY . .
COPY --from=php_build /app/vendor ./vendor
COPY --from=php_build /app/bootstrap/cache ./bootstrap/cache
COPY --from=php_build /app/public/vendor/statamic ./public/vendor/statamic
COPY --from=frontend_build /app/public/build ./public/build
COPY docker/frankenphp/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini
COPY docker/frankenphp/start-container.sh /usr/local/bin/start-container

RUN set -eux; \
    chmod +x /usr/local/bin/start-container; \
    for path in bootstrap/cache content database public resources/users storage users; do \
        if [ -e "${path}" ]; then \
            chown -R "${APP_USER}:${WWWGROUP}" "${path}"; \
        fi; \
    done

USER ${APP_USER}

ENTRYPOINT ["/usr/local/bin/start-container"]
CMD ["server"]
