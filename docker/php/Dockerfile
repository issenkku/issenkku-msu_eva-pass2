# ----------------------
# Node build stage
# ----------------------
FROM node:18 AS node-build
WORKDIR /app

# copy package files and install deps
COPY package.json package-lock.json ./
RUN npm ci --legacy-peer-deps

# copy all project files
COPY resources/ resources/
COPY vite.config.ts ./
COPY public/ public/
COPY tsconfig.json ./
# COPY tailwind.config.js ./

COPY . .

RUN npm install

# build vite assets
RUN npm run build

# ----------------------
# PHP base stage
# ----------------------
FROM php:8.3-fpm-alpine AS php-base

# System deps (add freetype, jpeg, png libs for GD)
RUN apk add --no-cache \
    curl git icu-dev libzip-dev libpng-dev oniguruma-dev openssl \
    zlib-dev build-base autoconf openssh-client \
    freetype-dev libjpeg-turbo-dev libwebp-dev

# PHP extensions (add GD extension)
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql mbstring exif bcmath zip intl gd

# Install composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Increase PHP memory limit
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory.ini

# Allow composer to run as root (temporary for build)
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Copy only composer files first for caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader

# Copy all application files
COPY . .

# Copy built assets from node-build
COPY --from=node-build /app/public/build /var/www/html/public/build

# Create Laravel directories
RUN mkdir -p bootstrap/cache storage/app storage/framework/{cache,sessions,views} storage/logs

# Create user
RUN addgroup -g 1000 appuser && adduser -u 1000 -G appuser -s /bin/sh -D appuser

# Set permissions
RUN chown -R appuser:appuser bootstrap/cache storage public/build

# Run full composer install
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Generate app key (skip if no .env yet)
RUN php artisan key:generate --ansi || echo "Skipping key generation - no .env file"

RUN php artisan config:cache || echo "Config cache failed"
RUN php artisan route:cache || echo "Route cache failed" 
RUN php artisan view:cache || echo "View cache failed"

# Final permissions
RUN chown -R appuser:appuser /var/www/html


# ----------------------
# Final production target
# ----------------------
FROM php-base AS php-prod
USER appuser
EXPOSE 9000
CMD ["php-fpm"]
