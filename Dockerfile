# ==========================================
# 階段一：安裝 Composer 依賴
# ==========================================
FROM composer:2 AS composer-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ==========================================
# 階段二：編譯前端資源 (支援 Laravel Wayfinder)
# ==========================================
FROM dunglas/frankenphp:1-php8.4-alpine AS node-builder
WORKDIR /app

# 在 PHP 環境中安裝 Node.js 與 npm
RUN apk add --no-cache nodejs npm

# 複製專案程式碼與階段一的 vendor（Wayfinder 執行 artisan 指令需要它）
COPY . .
COPY --from=composer-builder /app/vendor ./vendor

# 產生一個暫時的 .env 供 artisan 指令讀取（避免部分套件在 build 期報錯）
RUN cp .env.example .env 2>/dev/null || touch .env

RUN npm ci
RUN npm run build

# ==========================================
# 階段三：建立 FrankenPHP 正式運行映像檔
# ==========================================
FROM dunglas/frankenphp:1-php8.4-alpine

# 移除 FrankenPHP 執行檔的 Linux Capabilities，避免在 Render 觸發 Operation not permitted
RUN apk add --no-cache libcap \
    && setcap -r /usr/local/bin/frankenphp

# 安裝 Laravel 與 PostgreSQL 所需的 PHP 擴充套件
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    mbstring \
    xml \
    bcmath \
    curl \
    zip \
    gd \
    intl \
    opcache \
    redis

RUN install-php-extensions pcntl

WORKDIR /app

# 複製應用程式原始碼
COPY . .

# 從前兩階段複製編譯完成的依賴與前端產物
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build

# 確保 storage 與 cache 目錄存在並設定權限
RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             storage/logs \
             bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x /app/start.sh

# Render 預設會注入 PORT 環境變數，預設給 10000
ENV PORT=10000

EXPOSE 10000

ENTRYPOINT ["/app/start.sh"]
