<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedicalHistoryController;

Route::prefix('patients')->name('patients.')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/patients',    [PatientController::class, 'store'])
            ->middleware('permission:patients.create')->name('store');

        Route::get('/patients', [PatientController::class, 'index'])
            ->middleware('permission:patients.view')->name('index');

        Route::get('/patients/{id}',[PatientController::class, 'show'])
            ->middleware('permission:patients.view')->name('show');

        Route::put('/patients/{id}', [PatientController::class, 'update'])
            ->middleware('permission:patients.update')->name('update');

        Route::delete('/patients/{id}', [PatientController::class, 'destroy'])
            ->middleware('permission:patients.delete')->name('destroy');

        // Histórico clínico por paciente
        Route::get('/patients/{id}/medical-history',  [MedicalHistoryController::class, 'index'])
            ->middleware('permission:history.view')->name('history.index');

        Route::post('/patients/{id}/medical-history', [MedicalHistoryController::class, 'store'])
            ->middleware('permission:history.create')->name('history.store');
    });
