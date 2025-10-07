<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me',        [AuthController::class, 'me']);
    Route::post('/auth/logout',   [AuthController::class, 'logout']);     
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']); 
});

Route::middleware(['auth:sanctum','throttle:6,1'])->group (function () {
    Route::post('/email/verification-notification', [VerifyEmailController::class, 'send'])
        ->name('verification.send');
});

Route::middleware(['auth:sanctum','signed','throttle:6,1'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->name('verification.verify');
});


