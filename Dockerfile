# Use PHP 8.2 FPM (Debian-based)
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libxslt-dev \
    nodejs \
    npm \
    supervisor \
    nginx \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl xsl

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app code
COPY . /var/www/html
COPY --chown=www-data:www-data . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port for internal use
EXPOSE 9000