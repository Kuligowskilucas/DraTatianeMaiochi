<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

Route::prefix('me')->name('me.')
    ->middleware(['auth:sanctum','role:patient'])
    ->group(function () {
        Route::get('appointments', [AppointmentController::class, 'myAppointments'])->name('appointments');
        // Ex.: Route::get('profile', [MeController::class, 'profile'])->name('profile');
    });
