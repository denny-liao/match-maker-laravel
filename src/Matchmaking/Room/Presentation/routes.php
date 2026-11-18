<?php

use Illuminate\Support\Facades\Route;
use Src\Matchmaking\Room\Presentation\Controllers\RoomController;

Route::middleware('auth')->prefix('matchmaking')->name('matchmaking.')->group(function () {
    Route::get('/', [RoomController::class, 'index'])->name('index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{code}', [RoomController::class, 'show'])->name('rooms.show');
    Route::post('/rooms/{code}/disconnect', [RoomController::class, 'disconnect'])->name('rooms.disconnect');
    Route::post('/rooms/{code}/reconnect', [RoomController::class, 'reconnect'])->name('rooms.reconnect');
    Route::post('/rooms/{code}/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::post('/rooms/{code}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
    Route::post('/rooms/{code}/ready', [RoomController::class, 'toggleReady'])->name('rooms.ready');
    Route::post('/rooms/{code}/start', [RoomController::class, 'start'])->name('rooms.start');
    Route::post('/rooms/{code}/number', [RoomController::class, 'assignNumber'])->name('rooms.number');
    Route::post('/rooms/{code}/vote', [RoomController::class, 'vote'])->name('rooms.vote');
});
