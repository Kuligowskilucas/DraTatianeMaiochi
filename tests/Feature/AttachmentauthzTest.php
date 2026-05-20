<?php

use App\Models\MedicalRecordEntryAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;



beforeEach(function () {
    Storage::fake('private');
});

it('doctor autor sobe anexo válido na própria entry', function () {
    $helena  = actingAsRole('doctor');
    $patient = makePatient();
    bookAppointment($helena, $patient);
    $entry   = makeEntry($helena, $patient);

    $file = UploadedFile::fake()->image('exame.png', 200, 200);

    $response = $this->postJson(
        "/api/medical-record-entries/{$entry->id}/attachments",
        ['file' => $file],
    );

    $response->assertCreated()
        ->assertJsonPath('data.originalName', 'exame.png')
        ->assertJsonPath('data.mime', 'image/png');

    $this->assertDatabaseHas('medical_record_entry_attachments', [
        'medical_record_entry_id' => $entry->id,
        'uploaded_by'             => $helena->id,
        'mime'                    => 'image/png',
    ]);

    $stored = MedicalRecordEntryAttachment::first();
    Storage::disk('private')->assertExists($stored->file_path);
});

it('rejeita arquivo com mime falsificado (extensão .pdf mas conteúdo texto)', function () {
    $helena  = actingAsRole('doctor');
    $patient = makePatient();
    bookAppointment($helena, $patient);
    $entry   = makeEntry($helena, $patient);

    // .pdf no nome, mas conteúdo é texto puro — finfo detecta text/plain
    // e a regra `mimetypes:application/pdf,image/jpeg,image/png` rejeita.
    $file = UploadedFile::fake()->create('evil.pdf', 100, 'image/gif');

    $this->postJson(
        "/api/medical-record-entries/{$entry->id}/attachments",
        ['file' => $file],
    )->assertStatus(422)
     ->assertJsonValidationErrors(['file']);

    $this->assertDatabaseCount('medical_record_entry_attachments', 0);
});

it('doctor não autor da entry não consegue subir anexo', function () {
    /** @var User $helena */
    $helena = User::factory()->create();
    $helena->assignRole('doctor');

    /** @var User $bruno */
    $bruno = User::factory()->create();
    $bruno->assignRole('doctor');

    $patient = makePatient();
    bookAppointment($helena, $patient);
    bookAppointment($bruno,  $patient); // Bruno PODE ver, mas não escrever na entry da Helena

    $entry = makeEntry($helena, $patient);

    Sanctum::actingAs($bruno);

    $this->postJson(
        "/api/medical-record-entries/{$entry->id}/attachments",
        ['file' => UploadedFile::fake()->image('foto.jpg')],
    )->assertForbidden();

    $this->assertDatabaseCount('medical_record_entry_attachments', 0);
});

it('doctor não autor da entry não consegue deletar anexo', function () {
    /** @var User $helena */
    $helena = User::factory()->create();
    $helena->assignRole('doctor');

    /** @var User $bruno */
    $bruno = User::factory()->create();
    $bruno->assignRole('doctor');

    $patient = makePatient();
    bookAppointment($helena, $patient);
    bookAppointment($bruno,  $patient);

    $entry      = makeEntry($helena, $patient);
    $attachment = MedicalRecordEntryAttachment::create([
        'medical_record_entry_id' => $entry->id,
        'file_path'               => 'medical-record-attachments/'.$entry->id.'/seed.pdf',
        'original_name'           => 'seed.pdf',
        'mime'                    => 'application/pdf',
        'size'                    => 1024,
        'uploaded_by'             => $helena->id,
    ]);

    Sanctum::actingAs($bruno);

    $this->deleteJson("/api/medical-record-entry-attachments/{$attachment->id}")
        ->assertForbidden();

    expect($attachment->fresh()->trashed())->toBeFalse();
});

it('paciente recebe 403 ao listar anexos (sem permission medical_records.view)', function () {
    [, $patient] = actingAsPatient();

    // Cria doctor + appointment + entry + attachment no prontuário do próprio paciente
    /** @var User $helena */
    $helena = User::factory()->create();
    $helena->assignRole('doctor');
    bookAppointment($helena, $patient);

    $entry = makeEntry($helena, $patient);
    MedicalRecordEntryAttachment::create([
        'medical_record_entry_id' => $entry->id,
        'file_path'               => "medical-record-attachments/{$entry->id}/exam.pdf",
        'original_name'           => 'exam.pdf',
        'mime'                    => 'application/pdf',
        'size'                    => 2048,
        'uploaded_by'             => $helena->id,
    ]);

    // Paciente continua autenticado pelo helper actingAsPatient()
    $this->getJson("/api/medical-record-entries/{$entry->id}/attachments")
        ->assertForbidden();
});