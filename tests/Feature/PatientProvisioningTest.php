<?php

use App\Models\Patient;
use App\Models\User;

it('secretary cria paciente e provisiona usuario vinculado', function () {
    actingAsRole('secretary');

    $response = $this->postJson('/api/patients', [
        'name' => 'Paciente Novo',
        'email' => 'paciente.novo@teste.com',
        'phone' => '(11) 98888-7777',
        'birth_date' => '1990-05-21',
        'document' => '123.456.789-10',
        'notes' => 'Criado pela secretaria',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Paciente Novo')
        ->assertJsonPath('data.email', 'paciente.novo@teste.com')
        ->assertJsonPath('data.userId', fn ($value) => is_int($value) && $value > 0)
        ->assertJsonPath('tempPassword', fn ($value) => is_string($value) && strlen($value) >= 12);

    $patient = Patient::where('email', 'paciente.novo@teste.com')->firstOrFail();
    $user = User::findOrFail($patient->user_id);

    expect($user->email)->toBe('paciente.novo@teste.com')
        ->and($user->name)->toBe('Paciente Novo')
        ->and($user->is_active)->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->hasRole('patient'))->toBeTrue();
});

    it('patient role recebe permissions para ver suas consultas e seu historico', function () {
        $user = actingAsRole('patient');

        expect($user->hasPermissionTo('appointments.view_own'))->toBeTrue()
        ->and($user->hasPermissionTo('medical_records.view_own'))->toBeTrue();
    });

it('secretary nao cria paciente sem email porque nao ha como provisionar conta', function () {
    actingAsRole('secretary');

    $this->postJson('/api/patients', [
        'name' => 'Paciente Sem Email',
        'phone' => '(11) 97777-6666',
        'birth_date' => '1991-05-21',
        'notes' => 'Sem email não pode criar conta',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
});
