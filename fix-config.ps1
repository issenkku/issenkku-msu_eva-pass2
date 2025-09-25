Write-Host "Fixing Laravel configuration issues..." -ForegroundColor Green

# Clear all caches first
Write-Host "Clearing caches..." -ForegroundColor Yellow
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan cache:clear
docker exec laravel_app php artisan route:clear
docker exec laravel_app php artisan view:clear

# Check if Collision provider exists in config
Write-Host "Checking for Collision provider..." -ForegroundColor Yellow
$collision = docker exec laravel_app grep -c "CollisionServiceProvider" config/app.php 2>$null
if ($collision -gt 0) {
    Write-Host "Found Collision provider, removing it..." -ForegroundColor Yellow
    docker exec laravel_app sed -i '/CollisionServiceProvider/d' config/app.php
}

# Set proper environment variables
Write-Host "Setting proper logging configuration..." -ForegroundColor Yellow
docker exec laravel_app sh -c 'echo "LOG_CHANNEL=single" >> .env'
docker exec laravel_app sh -c 'echo "LOG_LEVEL=debug" >> .env'

# Try to run artisan commands to test
Write-Host "Testing configuration..." -ForegroundColor Yellow
docker exec laravel_app php artisan config:cache

Write-Host "Configuration fixed! Testing application..." -ForegroundColor Green