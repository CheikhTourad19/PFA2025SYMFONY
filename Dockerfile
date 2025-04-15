# Stage 1: Composer + dependencies
FROM composer:2 as composer

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install intl pdo pdo_mysql zip

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Stage 2: Final runtime PHP environment
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install intl pdo pdo_mysql zip

WORKDIR /var/www/html
COPY . .
COPY --from=composer /app/vendor ./vendor

RUN chmod -R 775 var vendor && \
    chown -R www-data:www-data var vendor

EXPOSE 9000
CMD ["php-fpm"]
