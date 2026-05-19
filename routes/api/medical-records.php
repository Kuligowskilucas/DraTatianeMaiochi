<?php

use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicalRecordEntryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Prontuário do paciente (1:1)
    Route::get('/patients/{patient}/medical-record', [MedicalRecordController::class, 'show'])
        ->middleware('permission:medical_records.view')
        ->name('medicalRecords.show');

    // Timeline de entries do paciente
    Route::get('/patients/{patient}/medical-record/entries', [MedicalRecordEntryController::class, 'index'])
        ->middleware('permission:medical_records.view')
        ->name('medicalRecordEntries.indexByPatient');

    // CRUD direto de entries
    Route::prefix('medical-record-entries')->name('medicalRecordEntries.')->group(function () {
        Route::post('/', [MedicalRecordEntryController::class, 'store'])
            ->middleware('permission:medical_records.create')
            ->name('store');
    
        Route::get('/{entry}', [MedicalRecordEntryController::class, 'show'])
            ->middleware('permission:medical_records.view')
            ->name('show');
    
        Route::get('/{entry}/activity', [MedicalRecordEntryController::class, 'activity'])
            ->middleware('permission:medical_records.view')
            ->name('activity');
    
        Route::put('/{entry}', [MedicalRecordEntryController::class, 'update'])
            ->middleware('permission:medical_records.update')
            ->name('update');
    
        Route::delete('/{entry}', [MedicalRecordEntryController::class, 'destroy'])
        ->middleware('permission:medical_records.delete')
        ->name('destroy');
});
});