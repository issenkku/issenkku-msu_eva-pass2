#!/bin/sh
set -eu

echo "Starting Laravel application..."

php artisan storage:link || true

RUN_MIGRATIONS_ON_BOOT="${RUN_MIGRATIONS_ON_BOOT:-false}"
if [ "$RUN_MIGRATIONS_ON_BOOT" = "true" ]; then
  echo "Running migrations..."
  php artisan migrate --force
else
  echo "Skipping migrations on boot (RUN_MIGRATIONS_ON_BOOT=false)."
fi

echo "Caching Laravel bootstrap data..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting application server on port 8000..."
exec php artisan serve --host=0.0.0.0 --port=8000
