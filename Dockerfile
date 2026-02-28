FROM composer:2.8 AS composer

FROM php:8.5-cli-alpine

RUN apk add --no-cache bash oniguruma-dev \
    && docker-php-ext-install mbstring

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install --no-interaction --no-progress --prefer-dist

COPY . .

RUN chmod +x bin/app
RUN mkdir -p var/cache

CMD ["tail", "-f", "/dev/null"]
