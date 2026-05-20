<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordEntryAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'medicalRecordEntryId' => $this->medical_record_entry_id,
            'originalName'         => $this->original_name,
            'mime'                 => $this->mime,
            'size'                 => $this->size,
            'uploadedBy'           => $this->whenLoaded('uploadedBy', fn () => [
                'id'   => $this->uploadedBy->id,
                'name' => $this->uploadedBy->name,
            ]),
            'createdAt'            => $this->created_at?->toISOString(),
        ];
    }
}