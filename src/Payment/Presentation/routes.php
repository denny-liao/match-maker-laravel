<?php

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Src\Payment\Presentation\Controllers\PaymentInfoController;
use Src\Payment\Presentation\Controllers\PaymentWebhookController;

    
Route::post('/webhooks/payments/ecpay', [PaymentWebhookController::class, 'handleEcpay'])
    ->withoutMiddleware([PreventRequestForgery::class]);

     

// 付款資訊頁
Route::inertia('/payments', 'Payments/Index');

Route::get('/payments/info/{trade_no}', PaymentInfoController::class)->name('payments.info');
