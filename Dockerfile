# -------- wkhtmltopdf base --------
FROM surnet/alpine-wkhtmltopdf:3.23.2-024b2b2-full AS wkhtml

# -------- App Image --------
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


# Install dependencies for wkhtmltopdf
RUN apt-get update && apt-get install -y \
    xfonts-75dpi \
    xfonts-base \
    fontconfig \
    libxrender1 \
    libxext6 \
    libssl1.1 \
    libjpeg62-turbo \
    && rm -rf /var/lib/apt/lists/*

# Download and install wkhtmltopdf stable (0.12.6)
RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.buster_amd64.deb \
    && dpkg -i wkhtmltox_0.12.6-1.buster_amd64.deb \
    && apt-get -f install -y \
    && mv /usr/local/bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf \
    && rm wkhtmltox_0.12.6-1.buster_amd64.deb

# Copy wkhtmltopdf binary from the dedicated image
COPY --from=wkhtml /bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf

# Ensure binary is executable
RUN chmod +x /usr/local/bin/wkhtmltopdf

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

EXPOSE 9000    