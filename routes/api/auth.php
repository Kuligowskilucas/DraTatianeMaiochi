<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->name('auth.')->group(function () {
    // públicas (rate limit)
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login',    [AuthController::class, 'login'])->name('login');
    });

    // autenticadas
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',          [AuthController::class, 'me'])->name('me');
        Route::post('/logout',     [AuthController::class, 'logout'])->name('logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('logout_all');
    });
});
