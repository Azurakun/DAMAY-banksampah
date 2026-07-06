# ==========================================
# STAGE 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy dependency configs
COPY package*.json vite.config.js ./

# Install npm dependencies
RUN npm ci --no-audit

# Copy source folders needed for asset compilation
COPY resources/ ./resources/
COPY public/ ./public/

# Compile assets
RUN npm run build

# ==========================================
# STAGE 2: PHP Application Environment
# ==========================================
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    git \
    unzip \
    libpng-dev \
    libzip-dev \
    zip \
    icu-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    bcmath \
    gd \
    zip \
    opcache \
    pcntl \
    intl \
    mbstring

# Copy Composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Nginx to run under the same user as PHP-FPM (www-data)
RUN sed -i 's/user nginx;/user www-data;/g' /etc/nginx/nginx.conf

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh

# Make entrypoint script executable
RUN chmod +x /entrypoint.sh

# Copy source files
COPY . .

# Copy compiled assets from node-builder stage
COPY --from=node-builder /app/public/build ./public/build

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Create storage structure and optimize permissions
RUN mkdir -p /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/database && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/database && \
    chmod -R 775 /var/www/html/storage /var/www/html/database

# Expose ports:
# 80   - Nginx (HTTP Web traffic)
# 8080 - Reverb (WebSocket server)
EXPOSE 80 8080

ENTRYPOINT ["/entrypoint.sh"]
