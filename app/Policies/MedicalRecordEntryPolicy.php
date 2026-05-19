<?php

namespace App\Policies;

use App\Models\MedicalRecordEntry;
use App\Models\Patient;
use App\Models\User;

class MedicalRecordEntryPolicy
{
    /**
     * Lista entries de um paciente — mesma lógica do viewForPatient do record.
     */
    public function viewAnyForPatient(User $user, Patient $patient): bool
    {
        return app(MedicalRecordPolicy::class)->viewForPatient($user, $patient);
    }

    /**
     * Ver uma entry específica — herda regra do paciente dono.
     */
    public function view(User $user, MedicalRecordEntry $entry): bool
    {
        $patient = $entry->medicalRecord?->patient;
        if (! $patient) return false;

        return app(MedicalRecordPolicy::class)->viewForPatient($user, $patient);
    }

    /**
     * Criar entry no prontuário de um paciente.
     * - Admin sempre
     * - Doctor com relação (appointment) com o paciente
     * Patient NUNCA cria entry.
     */
    public function createForPatient(User $user, Patient $patient): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $patient->appointments()
                ->where('doctor_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Editar entry — admin sempre; doctor só se for o autor.
     */
    public function update(User $user, MedicalRecordEntry $entry): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('doctor') && $entry->author_id === $user->id;
    }

    /**
     * Soft-deletar entry — só admin (compliance CFM).
     */
    public function delete(User $user, MedicalRecordEntry $entry): bool
    {
        return $user->hasRole('admin');
    }
}