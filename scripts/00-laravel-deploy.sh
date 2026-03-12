#!/bin/sh
set -eu

echo "Installing PHP dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --working-dir=/var/www/html

echo "Clearing old caches..."
php artisan optimize:clear

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Caching views..."
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force
