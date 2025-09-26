#!/bin/sh

echo "Starting Laravel application..."

# Run Laravel setup commands
echo "Running Laravel migrations and cache..."
php artisan migrate --force || echo "Migration failed, continuing..."
php artisan config:cache || echo "Config cache failed, continuing..."
php artisan route:cache || echo "Route cache failed, continuing..."
php artisan view:cache || echo "View cache failed, continuing..."

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf