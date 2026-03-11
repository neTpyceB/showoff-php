FROM composer:2.8 AS composer-bin

FROM php:8.5-fpm-alpine AS php-base

RUN apk add --no-cache bash fcgi oniguruma-dev mysql-client sqlite-dev linux-headers \
    && docker-php-ext-install mbstring pdo_mysql pdo_sqlite sockets

COPY --from=composer-bin /usr/bin/composer /usr/bin/composer
COPY docker/php/fpm/zz-app.conf /usr/local/etc/php-fpm.d/zz-app.conf

WORKDIR /var/www/app

FROM php-base AS vendor-prod

COPY composer.json composer.lock* ./
COPY packages ./packages

RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --classmap-authoritative

FROM php-base AS vendor-dev

COPY composer.json composer.lock* ./
COPY packages ./packages

RUN composer install --no-interaction --no-progress --prefer-dist

FROM php-base AS development

ENV APP_ENV=local \
    APP_DEBUG=1

COPY docker/php/conf.d/app.dev.ini /usr/local/etc/php/conf.d/99-app.ini
COPY --from=vendor-dev /var/www/app/vendor /var/www/app/vendor
COPY . .

RUN chmod +x bin/app bin/console \
    && mkdir -p var/cache var/log

EXPOSE 9000

CMD ["php-fpm", "-F"]

FROM php-base AS production

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY docker/php/conf.d/app.prod.ini /usr/local/etc/php/conf.d/99-app.ini
COPY --from=vendor-prod /var/www/app/vendor /var/www/app/vendor
COPY . .

RUN chmod +x bin/app bin/console \
    && mkdir -p var/cache var/log

EXPOSE 9000

CMD ["php-fpm", "-F"]
