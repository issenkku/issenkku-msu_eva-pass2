# ----------------------
# Node build stage
# ----------------------
FROM node:18 AS node-build
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --legacy-peer-deps

COPY resources/ resources/
COPY vite.config.ts ./
COPY public/ public/
COPY tsconfig.json ./
COPY . .

RUN npm run build

# ----------------------
# PHP production stage
# ----------------------
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl git icu-dev libzip-dev libpng-dev oniguruma-dev openssl \
    zlib-dev build-base autoconf openssh-client \
    freetype-dev libjpeg-turbo-dev libwebp-dev \
    nginx supervisor postgresql-dev

# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql pdo_pgsql mbstring exif bcmath zip intl gd

# Install composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Configure PHP
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory.ini

# Set working directory
WORKDIR /var/www/html

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader

# Copy application files
COPY . .

# Copy built assets from node stage
COPY --from=node-build /app/public/build /var/www/html/public/build

# Create directories and set permissions
RUN mkdir -p bootstrap/cache storage/app storage/framework/{cache,sessions,views} storage/logs \
    /var/log/nginx /var/lib/nginx/tmp /run/nginx /var/log/supervisor

# Create user
RUN addgroup -g 1000 appuser && adduser -u 1000 -G appuser -s /bin/sh -D appuser

# Finalize composer install
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Copy configuration files
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/php/start.sh /usr/local/bin/start.sh

# Configure nginx for single container
RUN sed -i 's/listen 80;/listen 8000;/' /etc/nginx/conf.d/default.conf && \
    sed -i 's/fastcgi_pass app:9000;/fastcgi_pass 127.0.0.1:9000;/' /etc/nginx/conf.d/default.conf && \
    rm -f /etc/nginx/conf.d/default.conf.default

# Set permissions
RUN chmod +x /usr/local/bin/start.sh && \
    chown -R appuser:appuser /var/www/html && \
    chown -R nginx:nginx /var/lib/nginx /var/log/nginx

EXPOSE 8000

CMD ["/usr/local/bin/start.sh"]