#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

COMPOSE_FILE="${COMPOSE_FILE:-docker-composer.production.yml}"
ENV_FILE="${ENV_FILE:-.env.production}"
BACKUP_DIR="${BACKUP_DIR:-backups/db}"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP_FILE="$BACKUP_DIR/db-$TIMESTAMP.sql"

if [[ ! -f "$COMPOSE_FILE" ]]; then
  echo "Compose file not found: $COMPOSE_FILE"
  exit 1
fi

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Environment file not found: $ENV_FILE"
  exit 1
fi

if grep -Eq '^APP_KEY=$' "$ENV_FILE"; then
  echo "APP_KEY is empty in $ENV_FILE"
  echo "Set APP_KEY before deploy (example: php artisan key:generate --show)"
  exit 1
fi

mkdir -p "$BACKUP_DIR"
export APP_ENV_FILE="$ENV_FILE"

DC=(docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE")

echo "Starting MySQL service..."
"${DC[@]}" up -d mysql

echo "Waiting for MySQL to become ready..."
for i in {1..30}; do
  if "${DC[@]}" exec -T mysql sh -lc 'mysqladmin ping -h localhost --silent'; then
    break
  fi
  if [[ "$i" -eq 30 ]]; then
    echo "MySQL did not become ready in time."
    exit 1
  fi
  sleep 2
done

echo "Creating DB backup: $BACKUP_FILE"
"${DC[@]}" exec -T mysql sh -lc 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqldump -u root --single-transaction --routines --triggers "$MYSQL_DATABASE"' > "$BACKUP_FILE"

if [[ ! -s "$BACKUP_FILE" ]]; then
  echo "Backup failed: file is empty."
  exit 1
fi

echo "Building and starting application services..."
"${DC[@]}" up -d --build app nginx worker

echo "Running Laravel deploy tasks..."
"${DC[@]}" exec -T app sh /var/www/html/scripts/00-laravel-deploy.sh

echo "Restarting queue workers..."
"${DC[@]}" exec -T app php artisan queue:restart || true

echo "Deployment completed successfully."
echo "Backup saved at: $BACKUP_FILE"
