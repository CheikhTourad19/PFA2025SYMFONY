FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev

# Installation des extensions PHP
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    intl \
    opcache

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Configuration PHP pour le développement
COPY docker/php/php.ini /usr/local/etc/php/conf.d/docker-php-config.ini 