#!/usr/bin/env bash
set -euo pipefail

echo "==> Composer (production)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Node / Vite (using vite.config.js)"
npm ci
npm run build

echo "==> Remove Vite hot file (must NOT exist in production)"
rm -f public/hot

echo "==> Ensure static assets are in public/"
mkdir -p public/images public/uploads/products

echo "==> Writable / uploads permissions"
chmod -R 775 writable 2>/dev/null || true
mkdir -p uploads public/uploads/products
chmod -R 775 uploads public/uploads 2>/dev/null || true

echo "==> Build complete"
