# Use the official PHP image with necessary extensions
FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the project files
COPY . .

WORKDIR /var/www/html
# Install PHP dependencies
RUN composer install --no-interaction
# Set permissions for Symfony
RUN chmod -R 775 var

# Set permissions for var and vendor directories
RUN chmod -R 775 var vendor && \
    chown -R www-data:www-data var vendor


# Expose port
EXPOSE 9000

CMD ["sh", "-c", "php bin/console messenger:consume async --no-interaction & php-fpm"]

