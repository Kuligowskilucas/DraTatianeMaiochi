<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class MedicalRecordPolicy
{
    /**
     * Pode visualizar o prontuário de um paciente?
     * - Admin sempre
     * - Doctor: precisa ter consulta marcada com o paciente
     * - Patient: só o próprio
     */
    public function viewForPatient(User $user, Patient $patient): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $patient->appointments()
                ->where('doctor_id', $user->id)
                ->exists();
        }

        if ($user->hasRole('patient')) {
            return $patient->user_id === $user->id;
        }

        return false;
    }
}