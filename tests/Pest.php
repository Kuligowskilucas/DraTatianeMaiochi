<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordEntry;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(Tests\TestCase::class, RefreshDatabase::class)
    ->beforeEach(function () {
        // Seeda roles/permissions a cada teste — RefreshDatabase zera a DB,
        // o seeder é idempotente (firstOrCreate) e chama forgetCachedPermissions().
        $this->seed(RolePermissionSeeder::class);
    })
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers globais
|--------------------------------------------------------------------------
*/

/**
 * Cria um usuário com a role passada, autentica via Sanctum e devolve o user.
 */
function actingAsRole(string $role): User
{
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole($role);
    Sanctum::actingAs($user);
    return $user;
}

/**
 * Cria um usuário paciente + Patient atrelado, autentica e devolve [user, patient].
 *
 * @return array{0: User, 1: Patient}
 */
function actingAsPatient(): array
{
    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('patient');

    /** @var Patient $patient */
    $patient = Patient::factory()->create([
        'user_id' => $user->id,
        'name'    => $user->name,
        'email'   => $user->email,
    ]);

    Sanctum::actingAs($user);
    return [$user, $patient];
}

/**
 * Cria um Patient sem user atrelado (gerenciado pela clínica).
 */
function makePatient(array $overrides = []): Patient
{
    return Patient::factory()->create($overrides);
}

/**
 * Garante que o doctor tem appointment com o paciente
 * (pré-requisito da policy pra ver/criar entry).
 */
function bookAppointment(User $doctor, Patient $patient, ?User $createdBy = null): Appointment
{
    return Appointment::factory()->create([
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
        'created_by' => ($createdBy ?? $doctor)->id,
    ]);
}

/**
 * Cria uma entry escrita por $author no prontuário de $patient.
 * Lazy-cria o MedicalRecord se necessário.
 */
function makeEntry(User $author, Patient $patient, array $overrides = []): MedicalRecordEntry
{
    $record = MedicalRecord::firstOrCreate(['patient_id' => $patient->id]);

    return MedicalRecordEntry::factory()->create(array_merge([
        'medical_record_id' => $record->id,
        'author_id'         => $author->id,
        'entry_type'        => 'CONSULTATION',
        'subjective'        => 'Queixa principal.',
    ], $overrides));
}