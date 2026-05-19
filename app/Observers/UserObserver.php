<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\User;

class UserObserver
{
    /**
     * Quando User é soft-deletado, soft-deleta o Patient atrelado.
     * Quando é force-deleted, o FK nullOnDelete já cuida do user_id;
     * o Patient sobrevive como "walk-in" (clinicamente correto).
     */
    public function deleted(User $user): void
    {
        if (method_exists($user, 'isForceDeleting') && $user->isForceDeleting()) {
            return;
        }

        Patient::where('user_id', $user->id)->delete();
    }

    /**
     * Quando User é restaurado, restaura o Patient atrelado também.
     */
    public function restored(User $user): void
    {
        Patient::onlyTrashed()
            ->where('user_id', $user->id)
            ->restore();
    }
}