# Copilot 專案指引

## 應用程式概述

這是整合交友與配對功能的響應式 Web 應用程式，支援桌面、平板及手機。產品功能包含部落格貼文與留言，以及使用 WebSocket 推播的廣播功能。新增功能時，請延續既有模組邊界，並讓伺服器透過 Inertia 傳遞頁面資料。

## 技術堆疊與專案現況

- 後端採 Laravel 13 單體架構、PHP 與 Composer；驗證使用 Laravel Fortify。
- 前端使用 Inertia 3、React 19、TypeScript、Tailwind CSS 4 與 Vite Plus（Vite 生態系）。UI 優先沿用 Shadcn 元件與 Tailwind 樣式；Shadcn 設定位於 `components.json`，UI 元件位於 `resources/js/components/ui/`。
- 部署以 `Dockerfile` 建置 FrankenPHP PHP 8.4 映像，並由 `start.sh` 執行 Laravel 快取、資料庫 migration 與 FrankenPHP 伺服器；部署目標為 Render，資料庫目標為 Supabase。
- Composer、GitHub Actions CI 與部署映像皆以 PHP 8.4 為最低版本；調整 PHP 版本需求時，請一併核對 Composer 約束、Dockerfile 與 CI。
- 專案規劃的後端／前端測試框架為 Pest／Vitest；但目前 Composer 測試依賴與 `phpunit.xml` 設定的是 PHPUnit，`package.json` 也尚未設定 Vitest。新增測試或調整指令前，先以目前依賴與設定為準；不要假設 Pest 或 Vitest 已可執行。
- `BROADCAST_CONNECTION` 在測試設定中為 `null`，`.env.example` 使用 `log`。新增 WebSocket 推播時，確認所需的廣播驅動、環境變數與部署服務已設定，不要把規劃中的即時功能視為已完成配置。

## 建置、測試與檢查

- `composer setup` 安裝 Composer／Node 依賴、建立 `.env`（若不存在）、執行 migrations 並建置前端。
- `composer ci:check` 執行前端檢查、TypeScript 型別檢查與 Composer 測試流程，是 CI 使用的入口。
- `composer test` 執行 Pint 格式檢查、PHPStan 與 Laravel 測試。
- `composer lint:check` 檢查 PHP 格式；`composer lint` 套用 Pint 格式。
- `composer types:check` 執行 PHPStan。
- `php artisan test` 執行 PHP 測試；可用 `php artisan test tests/Feature/ExampleTest.php` 執行單一檔案，或用 `php artisan test tests/Feature/ExampleTest.php --filter=test_returns_a_successful_response` 執行單一測試。
- `npm run check` 執行前端檢查；`npm run types:check` 執行 TypeScript 型別檢查；`npm run build` 建置前端資源。
- 上述是目前已配置的命令。待 Pest 或 Vitest 加入專案後，再依其實際設定更新對應測試指令。

## 後端架構與資料流

`src/` 以限界上下文與領域組織程式碼：`App/` 放置開發工具與應用程式層級功能，`Common/` 放置跨領域共用邏輯，其餘目錄依上下文與領域分組。領域模組通常包含：

- `Presentation/`：路由、控制器、Request 驗證及 HTTP／Inertia 轉換。
- `Application/`：協調使用案例，包含 Commands、Handlers、Queries、DTOs 與應用層例外。
- `Domain/`：實體、值物件、聚合根、領域規則、領域例外與 Repository 介面。
- `Infrastructure/`：Eloquent Models、Repository 實作及 Service Providers，負責連接外部技術。

典型寫入流程為前端請求 → Middleware → Request 驗證 → Controller 將輸入轉成 Command → Handler 協調 Repository 載入聚合根 → 領域物件執行業務規則 → Repository 儲存 → Controller 回應 → Inertia 更新畫面。需要廣播或領域事件時，從適當的應用／領域流程發起，並確認相應的基礎設施與 provider 已註冊。

讀取流程可由 Controller 直接呼叫 Query，將讀取結果或 DTO 傳給 Inertia，不必為單純查詢載入聚合根。Comments 範例以 PDO prepared SQL 建立讀取模型，而寫入透過 Repository；依照實際讀取需求沿用該模組的模式。

`bootstrap/app.php` 會自動載入符合 `src/*/*/Presentation/routes.php` 的功能路由並套用 `web` middleware。Service Provider 不會由這項機制自動載入；新增模組 provider 時，須登記在 `bootstrap/providers.php`，並在模組 provider 綁定 Repository 介面與實作。

## 前端架構與慣例

- `resources/js/pages/` 放置依業務頁面分組的 Inertia 頁面；頁面名稱須與後端 `Inertia::render()` 使用的名稱相符。
- `resources/js/features/` 放置業務功能元件，例如 `features/Comments/`；`resources/js/components/` 放置跨業務共用元件。複合元件可將實作放在各自的 `src/` 子目錄，並由外層 `index.ts` 匯出。
- `resources/js/components/ui/` 是 Shadcn UI 元件；新增 UI 前先確認是否已有可複用元件。
- `resources/js/hooks/` 放置共用 React hooks，`resources/js/lib/` 放置與狀態無關的工具函式，`resources/js/enums/` 與 `resources/js/types/` 放置共用列舉與 TypeScript 型別。
- 業務專屬元件留在對應的 feature；只有真正跨業務使用時才提升至共用 components。頁面負責組合 feature 元件，並將 Inertia props 傳入畫面。
- UI 應維持桌面、平板與手機的響應式呈現，優先使用既有 Tailwind CSS 與 Shadcn 設計模式。

## 驗證與部署

- Fortify 的驗證動作與設定位於 `src/Auth/`；調整驗證流程時，延續既有 Fortify 整合方式。
- `Dockerfile` 與 `start.sh` 是 Render 部署入口。修改啟動、快取或 migration 流程時，檢查兩者如何銜接及 Render 注入的 `PORT`。
- Supabase 與 WebSocket 的連線設定應透過環境變數及 Laravel 設定處理；不要提交憑證，也不要假設本機 SQLite 測試環境等同正式環境。
