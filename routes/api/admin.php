<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        Route::get('/users/trash', [UserController::class, 'trash'])->name('users.trash');

        // CRUD de usuários
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->withTrashed()->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::post('/users/{user}/restore', [UserController::class, 'restore'])->withTrashed()->name('users.restore');
        Route::delete('/users/{user}/force', [UserController::class, 'forceDestroy'])->withTrashed()->name('users.forceDestroy');

        // Papéis e permissões
        Route::post('/users/{user}/roles', [UserController::class, 'assignRole'])->name('users.assignRole');
        Route::post('/users/{user}/permissions', [UserController::class, 'givePermission'])->name('users.givePermission');
        Route::post('/users/{user}/password', [UserController::class, 'changePassword'])->name('users.changePassword');
        Route::patch('/users/{user}/status', [UserController::class, 'changeStatus'])->name('users.changeStatus');
    });