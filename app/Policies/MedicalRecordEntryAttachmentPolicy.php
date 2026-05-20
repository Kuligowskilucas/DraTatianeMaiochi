<?php

namespace App\Policies;

use App\Models\MedicalRecordEntry;
use App\Models\MedicalRecordEntryAttachment;
use App\Models\User;

class MedicalRecordEntryAttachmentPolicy
{
    /**
     * Listar anexos de uma entry — quem vê a entry vê os anexos.
     */
    public function viewAnyForEntry(User $user, MedicalRecordEntry $entry): bool
    {
        return app(MedicalRecordEntryPolicy::class)->view($user, $entry);
    }

    /**
     * Baixar/ver um anexo — herda view da entry pai.
     */
    public function view(User $user, MedicalRecordEntryAttachment $attachment): bool
    {
        $entry = $attachment->entry;
        if (! $entry) {
            return false;
        }

        return app(MedicalRecordEntryPolicy::class)->view($user, $entry);
    }

    /**
     * Anexar arquivo a uma entry — herda update da entry.
     * Admin sempre; doctor só se for o autor.
     * Patient nunca chega aqui (já barrado no view da entry mais acima).
     */
    public function createForEntry(User $user, MedicalRecordEntry $entry): bool
    {
        return app(MedicalRecordEntryPolicy::class)->update($user, $entry);
    }

    /**
     * Soft-deletar anexo — mesma regra do update da entry.
     * Admin sempre; doctor só nos próprios anexos (porque só consegue
     * editar a própria entry).
     */
    public function delete(User $user, MedicalRecordEntryAttachment $attachment): bool
    {
        $entry = $attachment->entry;
        if (! $entry) {
            return false;
        }

        return app(MedicalRecordEntryPolicy::class)->update($user, $entry);
    }
}