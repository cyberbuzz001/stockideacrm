# Production Dockerfile for Laravel CRM

# ---- Stage 1: PHP dependencies (composer install; discarded after this stage) ----
FROM composer:2 AS vendor
WORKDIR /var/www
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ---- Stage 2: frontend build (node/npm; discarded after this stage) ----
FROM node:20-bookworm-slim AS assets
WORKDIR /var/www
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 3: runtime ----
FROM php:8.2-fpm

# Install system dependencies
# (git and the Node.js toolchain are no longer needed here — composer install
# and npm run build now happen in their own stages above and never ship in
# this image)
RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www

# Copy application source
COPY . /var/www

# Pull in the build output from the earlier stages instead of rebuilding here
COPY --from=vendor /var/www/vendor ./vendor
COPY --from=assets /var/www/public/build ./public/build

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 80
EXPOSE 80

# Restart the container if nginx/php-fpm stop responding, instead of staying
# "up" while actually serving nothing
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fs http://localhost/ -o /dev/null || exit 1

# Start nginx and php-fpm
CMD service nginx start && php-fpm
