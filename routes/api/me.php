<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

Route::prefix('me')->name('me.')
    ->middleware(['auth:sanctum','role:patient'])
    ->group(function () {
        
        Route::get('/my-appointments', [AppointmentController::class, 'myAppointments'])
            ->middleware('role:patient')->name('myAppointments');

        // Route::get('/my-profile', [MeController::class, 'profile'])->name('myProfile');
        
    });
