#!/bin/sh
set -e

# 1. Pastikan symlink storage terhubung (Linux native symlink)
if [ ! -L "/var/www/html/public/storage" ]; then
    echo "Creating storage symlink..."
    rm -rf /var/www/html/public/storage
    php artisan storage:link || true
fi

# 2. Pastikan permission folder storage dan bootstrap/cache writable
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# 3. Jalankan command utama
exec "$@"