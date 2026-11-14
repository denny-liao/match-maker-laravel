#!/bin/sh
set -e

echo "正在最佳化 Laravel 快取..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "正在執行資料庫 Migration..."
php artisan migrate --force

echo "啟動 FrankenPHP 伺服器於 Port ${PORT:-10000}..."
exec frankenphp php-server \
    --root=/app/public \
    --listen=0.0.0.0:${PORT:-10000}
