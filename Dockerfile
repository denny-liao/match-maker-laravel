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
# 階段二：編譯前端資源 (React + Vite + Tailwind v4)
# ==========================================
FROM node:22-alpine AS node-builder
WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

# 複製前端編譯所需檔案（若有用到 Wayfinder/Ziggy 需一併帶入）
COPY vite.config.* tsconfig.json* components.json* ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ==========================================
# 階段三：建立 FrankenPHP 正式運行映像檔
# ==========================================
FROM dunglas/frankenphp:1-php8.4-alpine

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
    opcache

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
