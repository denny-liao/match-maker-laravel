<?php

namespace Src\Auth\Infrastructure\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 指定登入頁面渲染哪一個 Inertia React Component
        Fortify::loginView(function (Request $request) {
            return Inertia::render('Auth/Login', [
                'canResetPassword' => Route::has('password.request'),
                'status' => $request->session()->get('status'),
            ]);
        });
        
        // 指定註冊頁面
        Fortify::registerView(function () {
            return Inertia::render('Auth/Register');
        });
    }
}
