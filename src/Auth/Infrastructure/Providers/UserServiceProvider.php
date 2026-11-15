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
        // 1. 指定登入頁面渲染哪一個 Inertia React Component
        Fortify::loginView(function (Request $request) {
            return Inertia::render('Auth/Login', [
                'canResetPassword' => Route::has('password.request'),
                'status' => $request->session()->get('status'),
            ]);
        });

        // 2. 指定註冊頁面
        Fortify::registerView(function () {
            return Inertia::render('Auth/Register');
        });

        // 3. 忘記密碼頁
        Fortify::requestPasswordResetLinkView(function (Request $request) {
            return Inertia::render('Auth/ForgotPassword', [
                'status' => $request->session()->get('status'),
            ]);
        });

        // 4. 重設密碼頁 (必須把網址上的 token 與 email 傳給 React)
        Fortify::resetPasswordView(function (Request $request) {
            return Inertia::render('Auth/ResetPassword', [
                'email' => $request->input('email'),
                'token' => $request->route('token'),
            ]);
        });
    }
}
