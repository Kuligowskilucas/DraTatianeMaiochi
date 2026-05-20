<?php

use App\Models\Patient;
use App\Models\User;
use Laravel\Sanctum\Sanctum;



/*
|--------------------------------------------------------------------------
| Authz crítica
|--------------------------------------------------------------------------
| Cobre isolamento por role e propagação de soft delete.
| Sem Storage::fake aqui — testes de anexo ficam em AttachmentAuthzTest.
*/

it('paciente vê o próprio prontuário via /me/medical-record', function () {
    [, $patient] = actingAsPatient();

    $response = $this->getJson('/api/me/medical-record');

    $response->assertOk()
        ->assertJsonPath('data.patientId', $patient->id);
});

it('paciente não vê prontuário de outro paciente', function () {
    actingAsPatient();
    $outroPatient = makePatient();

    $this->getJson("/api/patients/{$outroPatient->id}/medical-record")
        ->assertForbidden();
});

it('doctor com appointment vê prontuário do paciente', function () {
    $doctor = actingAsRole('doctor');
    $patient = makePatient();
    bookAppointment($doctor, $patient);

    $this->getJson("/api/patients/{$patient->id}/medical-record")
        ->assertOk()
        ->assertJsonPath('data.patientId', $patient->id);
});

it('doctor sem appointment não vê prontuário do paciente', function () {
    actingAsRole('doctor');
    $patient = makePatient();

    // Não cria appointment — policy nega.
    $this->getJson("/api/patients/{$patient->id}/medical-record")
        ->assertForbidden();
});

it('doctor não autor da entry não recebe confidential_notes', function () {
    // Helena (autora) e Bruno (outro doctor) atendem o mesmo paciente.
    /** @var User $helena */
    $helena = User::factory()->create();
    $helena->assignRole('doctor');

    /** @var User $bruno */
    $bruno = User::factory()->create();
    $bruno->assignRole('doctor');

    $patient = makePatient();
    bookAppointment($helena, $patient);
    bookAppointment($bruno, $patient);

    $entry = makeEntry($helena, $patient, [
        'confidential_notes' => 'Hipótese diagnóstica reservada.',
    ]);

    Sanctum::actingAs($bruno);

    $response = $this->getJson("/api/medical-record-entries/{$entry->id}");
    $response->assertOk();

    $data = $response->json('data');
    expect($data)
        ->toHaveKey('subjective')              
        ->not->toHaveKey('confidentialNotes'); 
});

it('admin vê confidential_notes em qualquer entry', function () {
    /** @var User $helena */
    $helena = User::factory()->create();
    $helena->assignRole('doctor');

    $patient = makePatient();
    bookAppointment($helena, $patient);

    $entry = makeEntry($helena, $patient, [
        'confidential_notes' => 'Apenas para o psiquiatra responsável.',
    ]);

    actingAsRole('admin');

    $this->getJson("/api/medical-record-entries/{$entry->id}")
        ->assertOk()
        ->assertJsonPath('data.confidentialNotes', 'Apenas para o psiquiatra responsável.');
});

it('secretary não acessa /admin/users', function () {
    actingAsRole('secretary');

    $this->getJson('/api/admin/users')->assertForbidden();
    $this->getJson('/api/admin/users/trash')->assertForbidden();
});

it('soft delete em user cascateia para o patient atrelado', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('patient');

    $patient = Patient::factory()->create([
        'user_id' => $user->id,
        'name'    => $user->name,
        'email'   => $user->email,
    ]);

    $user->delete(); 

    expect($user->fresh()->trashed())->toBeTrue();
    expect(Patient::withTrashed()->find($patient->id)->trashed())->toBeTrue();

    $user->restore();
    expect($user->fresh()->trashed())->toBeFalse();
    expect(Patient::withTrashed()->find($patient->id)->trashed())->toBeFalse();
});