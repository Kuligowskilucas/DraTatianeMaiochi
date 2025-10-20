<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerifyEmailController;

Route::prefix('email')->name('verification.')->middleware('auth:sanctum')->group(function () {
    
    Route::post('verification-notification', [VerifyEmailController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('send');

    Route::get('verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed','throttle:6,1'])
        ->name('verify');

    Route::post('/forgot-password', [VerifyEmailController::class, 'forgotPassword'])
        ->name('forgotPassword');

    Route::post('/reset-password', [VerifyEmailController::class, 'resetPassword'])
        ->name('resetPassword');
});
