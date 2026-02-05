FROM php:8.2-fpm

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
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    xsl

RUN apt-get update && apt-get install -y \
    fontconfig \
    fonts-dejavu \
    fonts-liberation \
    fonts-noto \
    libfreetype6 \
    libjpeg62-turbo-dev \
    libpng-dev \
    libicu-dev \
    ghostscript \
    imagemagick \
    && docker-php-ext-install intl gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . .

# Create required Laravel directories
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Final permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

RUN apt-get update && apt-get install -y cron

EXPOSE 9000
CMD ["php-fpm"]