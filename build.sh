#!/usr/bin/env bash
set -euo pipefail

echo "==> Using production Vite config"
cp vite.config.production.js vite.config.js

echo "==> Composer (production)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Node / Vite"
npm ci
npm run build

echo "==> Remove Vite hot file (must NOT exist in production)"
rm -f public/hot

echo "==> Writable / uploads permissions"
chmod -R 775 writable 2>/dev/null || true
mkdir -p uploads public/uploads/products
chmod -R 775 uploads public/uploads 2>/dev/null || true

echo "==> Build complete"
