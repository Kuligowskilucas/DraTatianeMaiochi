<?php

use Illuminate\Support\Facades\Route;

// Exemplo de bloco que só libera acesso com e-mail verificado
Route::middleware(['auth:sanctum','verified'])->group(function () {
    // Route::get('dashboard/overview', [DashboardController::class, 'overview'])->name('dashboard.overview');
});
