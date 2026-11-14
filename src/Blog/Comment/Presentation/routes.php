<?php

use Src\Blog\Comment\Presentation\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CommentController::class, 'index']);

Route::post('/comments', [CommentController::class, 'store']);
// {comment}: route model binding，就是 id 主鍵
Route::get('/comments/{comment}/edit', [CommentController::class, 'edit']);
Route::put('/comments/{comment}', [CommentController::class, 'update']);
Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

