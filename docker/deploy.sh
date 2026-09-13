#!/bin/sh
set -e

echo "=== Memulai Proses Deployment Soulmate App ==="

# 1. Storage symlink
echo "[1/5] Linking storage..."
php artisan storage:link --force || true

# 2. Database Migration
echo "[2/5] Running database migrations..."
php artisan migrate --force

# 3. Cache Configurations, Routes, Views
echo "[3/5] Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Livewire & UI Assets
echo "[4/5] Optimizing assets..."
php artisan livewire:publish --assets 2>/dev/null || true

# 5. Fix file permissions
echo "[5/5] Setting correct permissions for storage and cache..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "=== Deployment Selesai Sukses! ==="