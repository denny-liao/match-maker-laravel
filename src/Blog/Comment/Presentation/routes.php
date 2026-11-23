<?php

use Src\Blog\Comment\Presentation\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use Src\Blog\Comment\Presentation\Controllers\DonateComment;

Route::get('/', [CommentController::class, 'index'])
    ->name('home');

// Protected routes
Route::middleware('auth')->group(function () {
    // 簡寫 + only 白名單 (用到的功能)，會生成如上面固定 URL 的路由
    Route::resource('comments', CommentController::class)
        ->only(['store', 'edit', 'update', 'destroy']);
});

Route::post('/comments/donations', DonateComment::class)
    ->name('comments.donations');
