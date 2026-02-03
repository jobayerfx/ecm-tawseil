# ---------- wkhtmltopdf build stage ----------
    FROM openlabs/docker-wkhtmltopdf:latest as wkhtml

    # ---------- PHP App build stage ----------
    FROM php:8.2-fpm
    
    WORKDIR /var/www/html
    
    # Install system deps
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
    
    # Copy wkhtmltopdf binary from working image
    COPY --from=wkhtml /usr/local/bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf
    
    # Install Composer
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    
    # Copy app
    COPY . .
    
    # Create Laravel dirs
    RUN mkdir -p storage bootstrap/cache \
        && chown -R www-data:www-data storage bootstrap/cache \
        && chmod -R 775 storage bootstrap/cache
    
    # Composer install
    RUN composer install --no-dev --optimize-autoloader
    
    # Final perms
    RUN chown -R www-data:www-data /var/www/html \
        && chmod -R 775 storage bootstrap/cache
    
    EXPOSE 9000
    