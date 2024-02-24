FROM php:8.3.3-fpm-alpine

RUN apk update && apk add \
    bash \
    curl


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk add libpq-dev
RUN docker-php-ext-install pdo pgsql pdo_pgsql

WORKDIR /var/www