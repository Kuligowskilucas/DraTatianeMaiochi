<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

Route::prefix('appointments')->name('appointments.')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/appointments',                [AppointmentController::class, 'index'])
            ->middleware('permission:appointments.view')->name('index');

        Route::post('/appointments',               [AppointmentController::class, 'store'])
            ->middleware('permission:appointments.create')->name('store');

        Route::get('/appointments/{id}',    [AppointmentController::class, 'show'])
            ->middleware('can:view,appointment')->name('show');

        Route::put('/appointments/{id}', [AppointmentController::class, 'update'])
            ->middleware('permission:appointments.update')->name('update');

        Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy'])
            ->middleware('permission:appointments.delete')->name('destroy');

        // confirmação de presença pelo paciente dono
        Route::post('/appointments/{id}/confirm', [AppointmentController::class, 'confirm'])
            ->middleware('role:patient')->name('confirm');
    });
