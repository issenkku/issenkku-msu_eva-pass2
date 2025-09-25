#!/usr/bin/env pwsh

Write-Host "=== Laravel Docker Setup Script ===" -ForegroundColor Green

# Use the correct filename
Write-Host "Starting containers..." -ForegroundColor Yellow
try {
    docker-compose -f docker-composer.production.yml up -d  # Correct filename
    if ($LASTEXITCODE -ne 0) {
        throw "Failed to start containers"
    }
} catch {
    Write-Host "Error starting containers: $_" -ForegroundColor Red
    exit 1
}

# Wait longer for MySQL to be ready
Write-Host "Waiting for containers to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Check if containers are running
Write-Host "Checking container status..." -ForegroundColor Yellow
docker-compose -f docker-composer.production.yml ps

# Test database connection before proceeding
Write-Host "Testing database connection..." -ForegroundColor Yellow
$maxAttempts = 10
$attempt = 0
do {
    $attempt++
    Write-Host "Attempt $attempt/$maxAttempts - Testing database..." -ForegroundColor Yellow
    $dbTest = docker exec laravel_app php -r "try { DB::connection()->getPdo(); echo 'OK'; } catch(Exception \$e) { echo 'FAIL'; }" 2>$null
    if ($dbTest -eq "OK") {
        Write-Host "Database connection successful!" -ForegroundColor Green
        break
    }
    Start-Sleep -Seconds 5
} while ($attempt -lt $maxAttempts)

if ($dbTest -ne "OK") {
    Write-Host "Database connection failed after $maxAttempts attempts" -ForegroundColor Red
    Write-Host "Check MySQL container logs: docker-compose -f docker-composer.production.yml logs mysql" -ForegroundColor Yellow
    exit 1
}

# Run setup commands
Write-Host "Running migrations..." -ForegroundColor Yellow
docker exec laravel_app php artisan migrate --force

Write-Host "Running seeders..." -ForegroundColor Yellow
docker exec laravel_app php artisan migrate --seed --force

Write-Host "Setup completed!" -ForegroundColor Green
Write-Host "You should now be able to login with seeded user data" -ForegroundColor Cyan