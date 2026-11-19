# 配對交友平台

整合配對房間與即時互動的 Web 應用程式。使用者可建立或加入房間，依序完成準備、輸入編號與投票；全部投票完成後，系統會顯示互相選擇成功的配對結果。

## 技術架構

- 後端：PHP 8.4、Laravel 13、Laravel Fortify
    - 架構：DDD、CQRS
- 前端：Inertia 3、React 19、TypeScript、Tailwind CSS 4
    - Design system：Shadcn 4
- 即時通訊：Laravel Reverb、Laravel Echo、Pusher JS
- 資料庫：本機可使用 SQLite；正式環境使用 PostgreSQL（Supabase）
- 部署：Docker、FrankenPHP，部署於 Render

後端依限界上下文劃分於 `src/`，並採用 Application、Domain、Infrastructure、Presentation 分層。配對房間功能位於 `src/Matchmaking/Room/`；前端頁面與功能分別位於 `resources/js/pages/Matchmaking/` 和 `resources/js/features/Matchmaking/`。

## 本機開發

需求：PHP 8.4（含 phpredis 擴充）、Composer、Node.js、npm，以及可連線的 Redis 服務。

首次安裝相依套件、建立環境檔、產生應用程式金鑰、執行資料庫遷移並建置前端：

```bash
composer setup
```

設定 `.env` 中的資料庫與 Reverb 連線資訊。Reverb 本機開發可使用 `127.0.0.1:8080` 和 `http`；`REVERB_APP_ID`、`REVERB_APP_KEY`、`REVERB_APP_SECRET` 必須在 Laravel 與 Reverb 伺服器端一致。請勿在正式環境使用 `.env.example` 的範例密鑰。

### Redis 快取

Laravel 快取預設使用 Redis（`CACHE_STORE=redis`），連線由 `REDIS_*` 環境變數設定。若在本機使用 Docker 啟動 Redis：

```powershell
docker run -d --name match-maker-redis -p 6379:6379 redis:7-alpine
```

本機 Laravel 若透過 Laragon/PHP 執行，需另外安裝並啟用與 PHP 版本相符的 phpredis 擴充；Dockerfile 已為應用程式映像安裝此擴充。`.env.example` 使用 `127.0.0.1:6379` 作為本機 Redis 位址。測試環境則使用 PHPUnit 設定的獨立快取，不需要 Redis 服務。

啟動本機開發服務：

```bash
composer run dev
```

房間狀態更新透過 Reverb 廣播；佇列工作需由 Queue Worker 處理。若分開啟動服務，請確保 Laravel、前端開發伺服器、`php artisan reverb:start` 與 `php artisan queue:work` 都在執行。

## 測試與檢查

```bash
# 執行 Laravel 測試
php artisan test

# PHP 格式檢查、PHPStan 與 Laravel 測試
composer test

# 前端檢查與 TypeScript 型別檢查
npm run check
npm run types:check

# 建置前端資源
npm run build
```

## Render 部署

專案使用同一個 Docker image 建立一般 Web Service 與 Reverb Web Service：

- **一般 Web Service**：使用預設 `SERVICE_ROLE=web`，啟動 FrankenPHP、Queue Worker，並執行資料庫遷移。
- **Reverb Web Service**：設定 `SERVICE_ROLE=reverb`，由 `start.sh` 使用 Render 注入的 `PORT` 啟動 Reverb。

兩個服務需設定一致的 `REVERB_APP_ID`、`REVERB_APP_KEY`、`REVERB_APP_SECRET`。一般 Web Service 的 `REVERB_HOST` 設為 Reverb 服務的公開主機名稱（不含協定）；公開連線通常使用 `REVERB_PORT=443` 與 `REVERB_SCHEME=https`。正式環境亦需設定 PostgreSQL/Supabase 連線、`APP_KEY`、`APP_URL` 及其他 Laravel 必要環境變數。

快取使用 Redis。Render 上請建立 Redis 相容的 Key Value 服務，並在一般 Web Service 的環境變數中設定 `CACHE_STORE=redis`，再依該服務提供的連線資訊設定 `REDIS_URL`，或設定 `REDIS_HOST`、`REDIS_PORT`、`REDIS_USERNAME`、`REDIS_PASSWORD`。若服務要求 TLS，請使用服務提供的 TLS 連線 URL/設定。不要將連線密碼提交到版本控制。此設定只切換快取儲存；Queue 與 Session 仍使用各自的 `QUEUE_CONNECTION`、`SESSION_DRIVER` 設定。

瀏覽器端的 Reverb 連線資訊由 Blade 頁面在執行時提供。`REVERB_APP_KEY` 是客戶端連線識別值；`REVERB_APP_SECRET` 僅供伺服器使用，不可暴露給前端或提交至版本控制。

## 相關文件

- [Laravel 文件](https://laravel.com/docs)
- [Inertia 文件](https://inertiajs.com)
- [Laravel Reverb 文件](https://laravel.com/docs/reverb)
- [Render 文件](https://render.com/docs)

## 授權

本專案依 MIT License 授權。
