<?php

use App\Models\Patient;

it('secretary nao vê prontuário de paciente via rota por id', function () {
    actingAsRole('secretary');

    $patient = makePatient();

    $this->getJson("/api/patients/{$patient->id}/medical-record")
        ->assertForbidden();
});

it('secretary nao consegue criar entry no prontuário', function () {
    actingAsRole('secretary');

    $patient = makePatient();

    $payload = [
        'patient_id' => $patient->id,
        'entry_type' => 'NOTE',
        'subjective' => 'Tentativa indevida de criar entry',
    ];

    $this->postJson('/api/medical-record-entries', $payload)
        ->assertForbidden();
});

it('secretary nao lista entries do prontuário', function () {
    actingAsRole('secretary');

    $patient = makePatient();

    $this->getJson("/api/patients/{$patient->id}/medical-record/entries")
        ->assertForbidden();
});

it('secretary nao vê entry individual por id', function () {
    // cria entry por doctor
    $doctor = actingAsRole('doctor');
    $patient = makePatient();
    bookAppointment($doctor, $patient);
    $entry = makeEntry($doctor, $patient);

    actingAsRole('secretary');

    $this->getJson("/api/medical-record-entries/{$entry->id}")
        ->assertForbidden();
});

it('secretary nao lista nem baixa anexos', function () {
    // cria entry e anexo por doctor
    $doctor = actingAsRole('doctor');
    $patient = makePatient();
    bookAppointment($doctor, $patient);
    $entry = makeEntry($doctor, $patient);

    $attachment = \App\Models\MedicalRecordEntryAttachment::create([
        'medical_record_entry_id' => $entry->id,
        'file_path'               => "medical-record-attachments/{$entry->id}/seed.pdf",
        'original_name'           => 'seed.pdf',
        'mime'                    => 'application/pdf',
        'size'                    => 1024,
        'uploaded_by'             => $doctor->id,
    ]);

    actingAsRole('secretary');

    $this->getJson("/api/medical-record-entries/{$entry->id}/attachments")
        ->assertForbidden();

    $this->getJson("/api/medical-record-entry-attachments/{$attachment->id}/download")
        ->assertForbidden();
});
