#!/usr/bin/env bash
set -e

PORT="${PORT:-8080}"

if grep -q '^Listen ' /etc/apache2/ports.conf; then
    sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
else
    echo "Listen ${PORT}" >> /etc/apache2/ports.conf
fi

sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# Run database migrations on startup
echo "Running database migrations..."
php spark migrate --force || echo "Warning: Migrations failed or already up to date"

exec apache2-foreground
