<?php

use App\Models\Patient;
use App\Models\User;

it('permite recadastrar paciente com o mesmo cpf e reativa usuario soft-deletado', function () {
    actingAsRole('secretary');

    $email = 'recadastro.paciente@teste.com';
    $document = '123.456.789-00';

    $trashedUser = User::factory()->create([
        'email' => $email,
        'name' => 'Paciente Antigo',
        'is_active' => false,
        'must_change_password' => false,
    ]);
    $trashedUser->delete();

    Patient::factory()->create([
        'email' => $email,
        'document' => $document,
        'user_id' => $trashedUser->id,
    ])->delete();

    $response = $this->postJson('/api/patients', [
        'name' => 'Paciente Novo',
        'email' => $email,
        'phone' => '(11) 98888-7777',
        'birth_date' => '1990-05-21',
        'document' => $document,
        'notes' => 'Recadastrado depois de exclusão',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', $email)
        ->assertJsonPath('data.document', $document)
        ->assertJsonPath('tempPassword', fn ($value) => is_string($value) && strlen($value) >= 12);

    $user = User::findOrFail($trashedUser->id);
    expect($user->trashed())->toBeFalse()
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->is_active)->toBeTrue();

    expect(Patient::where('email', $email)->count())->toBe(1);
});
