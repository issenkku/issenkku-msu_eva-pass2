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

COPY . .

RUN npm install

# build vite assets
RUN npm run build

# ----------------------
# PHP base stage
# ----------------------
FROM php:8.3-fpm-alpine AS php-base

# System deps (add freetype, jpeg, png libs for GD, nginx, supervisor, postgresql)
RUN apk add --no-cache \
    curl git icu-dev libzip-dev libpng-dev oniguruma-dev openssl \
    zlib-dev build-base autoconf openssh-client \
    freetype-dev libjpeg-turbo-dev libwebp-dev \
    nginx supervisor postgresql-dev

# PHP extensions (add PostgreSQL support for Render)
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql pdo_pgsql mbstring exif bcmath zip intl gd

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

# Skip Laravel caching during build (will be done at runtime)
# Generate app key (skip if no .env yet)
RUN php artisan key:generate --ansi || echo "Skipping key generation - no .env file"

# Final permissions
RUN chown -R appuser:appuser /var/www/html

# ----------------------
# Final production target (MODIFIED FOR RENDER)
# ----------------------
FROM php-base AS php-prod

# Stay as root to configure nginx and supervisor
USER root

# Create nginx directories and logs
RUN mkdir -p /var/log/nginx /var/lib/nginx/tmp /run/nginx /var/log/supervisor

# Copy and modify nginx config for single container
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Modify nginx config for Render (port 8000 and localhost php-fpm)
RUN sed -i 's/listen 80;/listen 8000;/' /etc/nginx/conf.d/default.conf && \
    sed -i 's/fastcgi_pass app:9000;/fastcgi_pass 127.0.0.1:9000;/' /etc/nginx/conf.d/default.conf

# Remove default nginx config
RUN rm -f /etc/nginx/conf.d/default.conf.default

# Create supervisor config to run both nginx and php-fpm
RUN echo '[supervisord]' > /etc/supervisor/conf.d/supervisord.conf && \
    echo 'nodaemon=true' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'user=root' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'logfile=/var/log/supervisor/supervisord.log' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'pidfile=/var/run/supervisord.pid' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'loglevel=info' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo '' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo '[program:php-fpm]' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'command=php-fpm --nodaemonize' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stderr_logfile=/dev/stderr' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stderr_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stdout_logfile=/dev/stdout' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stdout_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'user=appuser' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo '' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo '[program:nginx]' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'command=nginx -g "daemon off;"' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stderr_logfile=/dev/stderr' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stderr_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stdout_logfile=/dev/stdout' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stdout_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'user=root' >> /etc/supervisor/conf.d/supervisord.conf

# Create startup script
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo 'echo "Starting Laravel application..."' >> /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo '# Run Laravel setup commands' >> /usr/local/bin/start.sh && \
    echo 'echo "Running Laravel migrations and cache..."' >> /usr/local/bin/start.sh && \
    echo 'php artisan migrate --force || echo "Migration failed, continuing..."' >> /usr/local/bin/start.sh && \
    echo 'php artisan config:cache || echo "Config cache failed, continuing..."' >> /usr/local/bin/start.sh && \
    echo 'php artisan route:cache || echo "Route cache failed, continuing..."' >> /usr/local/bin/start.sh && \
    echo 'php artisan view:cache || echo "View cache failed, continuing..."' >> /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo 'echo "Starting supervisor..."' >> /usr/local/bin/start.sh && \
    echo 'exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

# Set final permissions
RUN chown -R appuser:appuser /var/www/html
RUN chown -R nginx:nginx /var/lib/nginx /var/log/nginx

EXPOSE 8000

CMD ["/usr/local/bin/start.sh"]