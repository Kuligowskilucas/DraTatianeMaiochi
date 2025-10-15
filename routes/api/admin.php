<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        // === CRUD de usuários ===
        Route::apiResource('users', UserController::class);

        Route::post('users', [UserController::class, 'store'])
            ->name('users.store');
    
        Route::get('users/{id}', [UserController::class, 'show'])
            ->name('users.show');

        Route::put('users/{id}', [UserController::class, 'update'])
            ->name('users.update');
        
        Route::delete('users/{id}', [UserController::class, 'destroy'])
            ->name('users.destroy');

        // === Papéis e Roles de User ===
        Route::post('users/{id}/roles', [UserController::class, 'syncRoles'])
            ->name('users.roles.sync');

        Route::post('users/{id}/permissions', [UserController::class, 'syncPermissions'])
            ->name('users.permissions.sync');

        Route::post('users/{id}/password', [UserController::class, 'resetPassword'])
            ->name('users.password.reset');

        // === Ativar/Desativar usuário ===
        Route::patch('users/{id}/status', [UserController::class, 'toggleStatus'])
            ->name('users.status.toggle');

        // === Tokens (Sanctum) — listar e revogar ===
        Route::get('users/{id}/tokens', [UserController::class, 'tokensIndex'])
            ->name('users.tokens.index');

        Route::delete('users/{id}/tokens', [UserController::class, 'tokensRevokeAll'])
            ->name('users.tokens.revoke_all');
    });
