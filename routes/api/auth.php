<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:60,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',          [AuthController::class, 'me'])->name('me');
        Route::post('/logout',     [AuthController::class, 'logout'])->name('logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('logout_all');
        Route::put('/profile',     [AuthController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password',    [AuthController::class, 'changePassword'])->name('password.change');
    });
});