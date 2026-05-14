<?php

use App\Http\Controllers\DoctorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin|secretary'])
    ->get('/doctors', [DoctorController::class, 'index'])
    ->name('doctors.index');