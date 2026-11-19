#!/bin/sh
set -e

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

echo "啟動 Laravel Reverb..."
php artisan reverb:start \
    --host=127.0.0.1 \
    --port="${REVERB_SERVER_PORT:-8080}" &
REVERB_PID=$!

echo "啟動 FrankenPHP 伺服器於 Port ${PORT:-10000}..."
PORT="${PORT:-10000}" frankenphp run --config /app/Caddyfile &
WEB_SERVER_PID=$!

shutdown() {
    trap - TERM INT
    kill "$QUEUE_WORKER_PID" "$REVERB_PID" "$WEB_SERVER_PID" 2>/dev/null || true
    wait "$QUEUE_WORKER_PID" 2>/dev/null || true
    wait "$REVERB_PID" 2>/dev/null || true
    wait "$WEB_SERVER_PID" 2>/dev/null || true
}

trap 'shutdown; exit 0' TERM INT

check_process() {
    PROCESS_PID=$1
    PROCESS_NAME=$2

    if ! kill -0 "$PROCESS_PID" 2>/dev/null; then
        if wait "$PROCESS_PID"; then
            STATUS=1
        else
            STATUS=$?
        fi
        echo "$PROCESS_NAME exited with status $STATUS." >&2
        shutdown
        exit "$STATUS"
    fi
}

while :; do
    check_process "$QUEUE_WORKER_PID" "Queue Worker"
    check_process "$REVERB_PID" "Reverb"
    check_process "$WEB_SERVER_PID" "FrankenPHP"
    sleep 1
done
