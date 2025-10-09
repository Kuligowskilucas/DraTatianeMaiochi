<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
       if ($user->hasRole('admin') || $user->hasRole('secretary')) return true;

       // paciente: com user_id vinculado ao patient
        if ($user->hasRole('patient') && $appointment->patient?->user_id === $user->id) {
            return true;
        }
        return false;
    }

}
