#!/bin/sh
set -e

echo "=== Memulai Proses Deployment Soulmate App ==="

# 1. Install / Update PHP dependencies via Composer
echo "[1/6] Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 2. Pastikan APP_KEY terpasang jika belum ada
if ! grep -E -q "^APP_KEY=base64:[A-Za-z0-9+/=]+" /var/www/html/.env 2>/dev/null; then
    echo "[2/6] Generating APP_KEY (first time setup)..."
    php artisan key:generate --force
else
    echo "[2/6] APP_KEY already configured. Skipping key generation..."
fi

# 3. Storage symlink (Linux native)
echo "[3/6] Linking storage directory..."
php artisan storage:link --force || true

# 4. Database Migration
echo "[4/6] Running database migrations..."
php artisan migrate --force

# 5. Cache Configurations, Routes, Views, Events
echo "[5/6] Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Set File Permissions
echo "[6/6] Setting correct permissions for storage and cache..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "=== Deployment Selesai Sukses! ==="