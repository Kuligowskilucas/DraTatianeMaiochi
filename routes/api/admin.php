<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        // CRUD de usuários
        Route::apiResource('users', UserController::class);

        // Papéis (roles) e permissões de um usuário
        Route::post('users/{user}/roles', [UserController::class, 'syncRoles'])
            ->name('users.roles.sync');

        Route::post('users/{user}/permissions', [UserController::class, 'syncPermissions'])
            ->name('users.permissions.sync');

        // Reset de senha (admin define nova)
        Route::post('users/{user}/password', [UserController::class, 'resetPassword'])
            ->name('users.password.reset');

        // Ativar/Desativar usuário
        Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])
            ->name('users.status.toggle');

        // Tokens (Sanctum) — listar e revogar
        Route::get('users/{user}/tokens', [UserController::class, 'tokensIndex'])
            ->name('users.tokens.index');

        Route::delete('users/{user}/tokens', [UserController::class, 'tokensRevokeAll'])
            ->name('users.tokens.revoke_all');

        Route::delete('users/{user}/tokens/{tokenId}', [UserController::class, 'tokensRevokeOne'])
            ->name('users.tokens.revoke_one');
    });
