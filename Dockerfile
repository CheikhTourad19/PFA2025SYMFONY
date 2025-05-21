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
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        zip \
        gd \
        mbstring \
        exif \
        opcache \
        xml

# Configure opcache
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html



# Copy composer files first for better layer caching
COPY composer.json  ./

# Install dependencies (only)
RUN composer install --no-interaction --prefer-dist --verbose

# Copy the rest of the application code
COPY . .



# Generate optimized autoloader
RUN composer dump-autoload --optimize



# Expose port for php-fpm
EXPOSE 9002

# Default command
CMD ["php-fpm"]