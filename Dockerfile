FROM composer:latest AS composer
WORKDIR /app
COPY ./composer.json ./

RUN composer require fakerphp/faker phpmailer/phpmailer --no-update

RUN composer install --ignore-platform-reqs

FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        libgif-dev \
        libfreetype6-dev && \
    docker-php-ext-configure gd --with-jpeg --with-freetype && \
    docker-php-ext-install gd pgsql pdo pdo_pgsql && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug

RUN echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini
RUN echo "xdebug.mode=develop, debug" >> /usr/local/etc/php/php.ini
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/php.ini
RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/php.ini
RUN echo "xdebug.client_port=9003" >> /usr/local/etc/php/php.ini
RUN echo "xdebug.discover_client_host=0" >> /usr/local/etc/php/php.ini

WORKDIR /var/www/html

COPY --from=composer /app/vendor /var/www/html/vendor

EXPOSE 80
