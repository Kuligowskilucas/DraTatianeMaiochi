<?php

use App\Models\User;

it('permite recriar usuario admin ou secretary com o mesmo email apos exclusao', function () {
    actingAsRole('admin');

    $email = 'usuario.excluido@teste.com';

    $trashedUser = User::factory()->create([
        'email' => $email,
        'name' => 'Usuario Antigo',
        'is_active' => false,
    ]);
    $trashedUser->delete();

    $response = $this->postJson('/api/admin/users', [
        'name' => 'Usuario Reaproveitado',
        'email' => $email,
        'password' => 'Password@123',
        'password_confirmation' => 'Password@123',
        'roles' => ['secretary'],
        'is_active' => true,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', $email)
        ->assertJsonPath('data.name', 'Usuario Reaproveitado');

    $user = User::findOrFail($trashedUser->id);
    expect($user->trashed())->toBeFalse()
        ->and($user->name)->toBe('Usuario Reaproveitado')
        ->and($user->is_active)->toBeTrue()
        ->and($user->hasRole('secretary'))->toBeTrue();
});

it('permite editar usuario soft-deletado e o restaura automaticamente', function () {
    actingAsRole('admin');

    $trashedUser = User::factory()->create([
        'name' => 'Usuario Excluido',
        'email' => 'editado.excluido@teste.com',
        'is_active' => false,
    ]);
    $trashedUser->delete();

    $response = $this->putJson("/api/admin/users/{$trashedUser->id}", [
        'name' => 'Usuario Reeditado',
        'email' => 'editado.excluido@teste.com',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Usuario Reeditado')
        ->assertJsonPath('data.email', 'editado.excluido@teste.com');

    $user = User::findOrFail($trashedUser->id);
    expect($user->trashed())->toBeFalse()
        ->and($user->name)->toBe('Usuario Reeditado');
});
