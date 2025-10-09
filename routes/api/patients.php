<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedicalHistoryController;

Route::prefix('patients')->name('patients.')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/',            [PatientController::class, 'index'])
            ->middleware('permission:patients.view')->name('index');

        Route::post('/',           [PatientController::class, 'store'])
            ->middleware('permission:patients.create')->name('store');

        Route::get('{patient}',    [PatientController::class, 'show'])
            ->middleware('permission:patients.view')->name('show');

        Route::put('{patient}',    [PatientController::class, 'update'])
            ->middleware('permission:patients.update')->name('update');

        Route::delete('{patient}', [PatientController::class, 'destroy'])
            ->middleware('permission:patients.delete')->name('destroy');

        // Histórico clínico por paciente
        Route::get('{patient}/history',  [MedicalHistoryController::class, 'index'])
            ->middleware('permission:history.view')->name('history.index');

        Route::post('{patient}/history', [MedicalHistoryController::class, 'store'])
            ->middleware('permission:history.create')->name('history.store');
    });
