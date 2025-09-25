# ----------------------
# Node build stage (Vite assets)
# ----------------------
FROM node:18 AS node-build
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --legacy-peer-deps

COPY resources/ resources/
COPY vite.config.ts tsconfig.json ./
COPY public/ public/
COPY . .

RUN npm install
RUN npm run build

# ----------------------
# PHP + Nginx base
# ----------------------
FROM php:8.3-fpm-alpine AS php-base

# System dependencies
RUN apk add --no-cache \
    curl git icu-dev libzip-dev libpng-dev oniguruma-dev openssl \
    zlib-dev build-base autoconf openssh-client \
    freetype-dev libjpeg-turbo-dev libwebp-dev \
    nginx supervisor bash

# PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql mbstring exif bcmath zip intl gd

# Install composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Copy composer files first (for caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader

# Copy full project
COPY . .

# Copy built assets from Node
COPY --from=node-build /app/public/build /var/www/html/public/build

# Laravel dirs
RUN mkdir -p bootstrap/cache storage/app storage/framework/{cache,sessions,views} storage/logs

# Permissions
RUN addgroup -g 1000 appuser && adduser -u 1000 -G appuser -s /bin/sh -D appuser
RUN chown -R appuser:appuser /var/www/html /run/nginx /var/lib/nginx

# Install optimized composer deps
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Cache Laravel
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# ----------------------
# Final stage
# ----------------------
FROM php-base AS php-prod

USER root

# Copy Nginx config
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy Supervisor config
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

USER appuser

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
