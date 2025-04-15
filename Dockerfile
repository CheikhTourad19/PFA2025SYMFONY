# Stage 1: Build PHP dependencies with Composer
FROM composer:2 as composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Stage 2: PHP + Symfony runtime environment
FROM php:8.2-fpm

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Copy vendor from Composer stage
COPY --from=composer /app/vendor ./vendor

# Set permissions
RUN chmod -R 775 var vendor && \
    chown -R www-data:www-data var vendor

EXPOSE 9000
CMD ["php-fpm"]
