#!/bin/sh
set -e

# Run this image as a separate Render Web Service with SERVICE_ROLE=reverb.
if [ "${SERVICE_ROLE:-web}" = "reverb" ]; then
    php artisan config:clear
    php artisan config:cache
    exec php artisan reverb:start \
        --host=0.0.0.0 \
        --port="${PORT:-10000}"
fi

if [ "${SERVICE_ROLE:-web}" != "web" ]; then
    echo "Unsupported SERVICE_ROLE: ${SERVICE_ROLE}" >&2
    exit 64
fi

echo "正在最佳化 Laravel 快取..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "正在執行資料庫 Migration..."
php artisan migrate --force

echo "啟動 Laravel Queue Worker..."
php artisan queue:work --sleep=1 --tries=3 --timeout=90 &
QUEUE_WORKER_PID=$!

echo "啟動 FrankenPHP 伺服器於 Port ${PORT:-10000}..."
frankenphp php-server \
    --root=/app/public \
    --listen=0.0.0.0:${PORT:-10000} &
WEB_SERVER_PID=$!

shutdown() {
    kill "$QUEUE_WORKER_PID" "$WEB_SERVER_PID" 2>/dev/null || true
}

trap shutdown TERM INT

if wait "$WEB_SERVER_PID"; then
    STATUS=0
else
    STATUS=$?
fi
shutdown
wait "$QUEUE_WORKER_PID" 2>/dev/null || true
exit "$STATUS"
