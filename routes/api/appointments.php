<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

Route::prefix('appointments')->name('appointments.')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/',                [AppointmentController::class, 'index'])
            ->middleware('permission:appointments.view')->name('index');

        Route::post('/',               [AppointmentController::class, 'store'])
            ->middleware('permission:appointments.create')->name('store');

        Route::get('{appointment}',    [AppointmentController::class, 'show'])
            ->middleware('can:view,appointment')->name('show');

        Route::put('{appointment}',    [AppointmentController::class, 'update'])
            ->middleware('permission:appointments.update')->name('update');

        Route::delete('{appointment}', [AppointmentController::class, 'destroy'])
            ->middleware('permission:appointments.delete')->name('destroy');

        // confirmação de presença pelo paciente dono
        Route::post('{appointment}/confirm', [AppointmentController::class, 'confirm'])
            ->middleware('role:patient')->name('confirm');
    });
