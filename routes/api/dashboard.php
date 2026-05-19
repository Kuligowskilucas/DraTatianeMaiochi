<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->get('/dashboard/overview', [DashboardController::class, 'overview'])
    ->name('dashboard.overview');