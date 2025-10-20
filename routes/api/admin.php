<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        // === CRUD de usuários ===
        Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');

        Route::get('/admin/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // === Papéis e Roles de User ===
        Route::post('/admin/users/{id}/roles', [UserController::class, 'assignRole'])->name('users.assignRole');
        Route::post('/admin/users/{id}/permissions', [UserController::class, 'givePermission'])->name('users.givePermission');
        Route::post('/admin/users/{id}/password', [UserController::class, 'changePassword'])->name('users.changePassword');
        Route::patch('/admin/users/{id}/status', [UserController::class, 'changeStatus'])->name('users.changeStatus');

    });
