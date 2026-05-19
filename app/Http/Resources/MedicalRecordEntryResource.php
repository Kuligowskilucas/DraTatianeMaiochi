<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        // confidential_notes só visíveis pra admin ou autor da entry.
        // Outros doctors veem a entry mas a chave nem aparece no JSON.
        $canSeeConfidential = $user && (
            $user->hasRole('admin') || $user->id === $this->author_id
        );

        return [
            'id'                => $this->id,
            'medicalRecordId'   => $this->medical_record_id,
            'authorId'          => $this->author_id,
            'authorName'        => $this->whenLoaded('author', fn () => $this->author?->name),
            'appointmentId'     => $this->appointment_id,
            'appointmentDate'   => $this->whenLoaded(
                'appointment',
                fn () => $this->appointment?->starts_at?->toISOString()
            ),
            'entryType'         => $this->entry_type,
            'subjective'        => $this->subjective,
            'objective'         => $this->objective,
            'assessment'        => $this->assessment,
            'plan'              => $this->plan,
            'confidentialNotes' => $this->when($canSeeConfidential, $this->confidential_notes),
            'createdAt'         => $this->created_at?->toISOString(),
            'updatedAt'         => $this->updated_at?->toISOString(),
        ];
    }
}