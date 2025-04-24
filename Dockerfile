# Use the official PHP image with necessary extensions
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl pdo pdo_mysql zip gd

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions for Symfony
RUN chmod -R 775 var vendor && \
    chown -R www-data:www-data var vendor

# Expose port for php-fpm
EXPOSE 9002

# Default command
CMD ["php-fpm"]
