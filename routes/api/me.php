<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicalRecordEntryController;

Route::prefix('me')->name('me.')
    ->middleware(['auth:sanctum', 'role:patient'])
    ->group(function () {

        Route::get('/my-appointments', [AppointmentController::class, 'myAppointments'])
            ->name('myAppointments');

        Route::get('/medical-record', [MedicalRecordController::class, 'myRecord'])
            ->name('myMedicalRecord');

        Route::get('/medical-record/entries', [MedicalRecordEntryController::class, 'myEntries'])
            ->name('myMedicalRecordEntries');
    });