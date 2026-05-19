<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Ver uma consulta específica.
     * - Admin/secretary: sempre
     * - Doctor: só as próprias (doctor_id === user.id)
     * - Patient: só se for dono do patient atrelado
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('secretary')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $appointment->doctor_id === $user->id;
        }

        if ($user->hasRole('patient')) {
            return $appointment->patient?->user_id === $user->id;
        }

        return false;
    }

    /**
     * Editar uma consulta.
     * - Admin/secretary: sempre
     * - Doctor: só as próprias
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('secretary')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return $appointment->doctor_id === $user->id;
        }

        return false;
    }

    /**
     * Soft-deletar uma consulta.
     * Mesma regra do update.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->update($user, $appointment);
    }

    /**
     * Cancelar — mesma regra do update.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->update($user, $appointment);
    }

    /**
     * Confirmar presença — só o paciente dono.
     */
    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('patient')
            && $appointment->patient?->user_id === $user->id;
    }
}