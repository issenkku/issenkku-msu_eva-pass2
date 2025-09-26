#!/bin/sh

echo "Starting Laravel application..."

# Run Laravel setup commands
echo "Running Laravel migrations and cache..."
php artisan migrate --seed --force || echo "Migration failed, continuing..."
php artisan config:cache || echo "Config cache failed, continuing..."
php artisan route:cache || echo "Route cache failed, continuing..."
php artisan view:cache || echo "View cache failed, continuing..."

echo "Starting PHP development server on port 8000..."
exec php artisan serve --host=0.0.0.0 --port=8000