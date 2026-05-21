<?php

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Carbon;

it('retorna o startsAt da consulta no horario local da clinica', function () {
    Carbon::setTestNow(Carbon::parse('2026-05-21 12:00:00', 'America/Sao_Paulo'));

    /** @var User $secretary */
    $secretary = actingAsRole('secretary');

    /** @var User $doctor */
    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    /** @var Patient $patient */
    $patient = makePatient();

    $response = $this->postJson('/api/appointments', [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'starts_at' => '2026-05-21T16:20:00-03:00',
        'duration_minutes' => 60,
        'location' => 'Consultório 1',
        'notes' => 'Teste de fuso horário',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.startsAt', '2026-05-21T19:20:00.000000Z');

    Carbon::setTestNow();
});
