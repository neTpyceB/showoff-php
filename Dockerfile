FROM composer:2.8 AS composer

FROM php:8.5-fpm-alpine

RUN apk add --no-cache bash fcgi oniguruma-dev mysql-client sqlite-dev \
    && docker-php-ext-install mbstring pdo_mysql pdo_sqlite

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app

COPY composer.json composer.lock* ./
COPY packages ./packages

RUN composer install --no-interaction --no-progress --prefer-dist

COPY . .
COPY docker/php/conf.d/app.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/fpm/zz-app.conf /usr/local/etc/php-fpm.d/zz-app.conf

RUN chmod +x bin/app \
    && mkdir -p var/cache var/log

EXPOSE 9000

CMD ["php-fpm", "-F"]
