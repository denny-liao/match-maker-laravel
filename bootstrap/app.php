<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Src\Common\Presentation\Middleware\HandleInertiaRequests;
use Src\Matchmaking\Room\Domain\Exceptions\RoomRuleException;
use Symfony\Component\Finder\Finder;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            $files = Finder::create()
                ->files()
                ->in(base_path('src'))
                ->path('#(^|/)Presentation/#') // 確保檔案的上一層資料夾名稱嚴格為 Presentation
                ->name('routes.php');
            foreach ($files as $file) {
                Route::middleware('web')->group($file->getRealPath());
            }
        },
    )
    ->withCommands([
        __DIR__ . '/../src/App/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // 信任 Render 的反向代理，讓 Laravel 正確識別 HTTPS
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            // 如果想在終端機 (php artisan pail) 看錯誤訊息：
            // logger()->error($e->getMessage());

            // 如果想直接在瀏覽器彈窗看到完整的 Exception 細節：
            // dd($e->getMessage(), $e);
        });
        $exceptions->render(function (RoomRuleException $exception) {
            return back()->withErrors(['room' => $exception->getMessage()]);
        });
    })->create()
    ->useAppPath(dirname(__DIR__) . '/src');
