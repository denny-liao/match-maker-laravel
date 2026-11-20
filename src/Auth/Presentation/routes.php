<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', fn() => Inertia::render('Profile/Index'))->name('index');
});
