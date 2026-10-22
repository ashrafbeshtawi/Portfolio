# syntax=docker/dockerfile:1

# Single self-contained app image: PHP-FPM + Nginx supervised in one container.
# Targets:
#   dev  — used by docker-compose; source code is bind-mounted, Chromium included for E2E tests
#   prod — published to the registry; code and dependencies baked in

FROM php:8.4.27-fpm-alpine AS base

RUN apk add --no-cache nginx supervisor icu-libs libpq libzip git unzip \
    && apk add --no-cache --virtual .build-deps icu-dev libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl pdo_pgsql opcache zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY docker/php/app.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/php/supervisord.conf /etc/supervisord.conf
COPY docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80
ENTRYPOINT ["docker-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]


FROM base AS dev

ENV APP_ENV=dev

# Chromium + chromedriver for Panther E2E tests inside the container
RUN apk add --no-cache chromium chromium-chromedriver
ENV PANTHER_NO_SANDBOX=1 \
    PANTHER_CHROME_ARGUMENTS="--disable-dev-shm-usage"


FROM base AS prod

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-progress --no-interaction

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data var
