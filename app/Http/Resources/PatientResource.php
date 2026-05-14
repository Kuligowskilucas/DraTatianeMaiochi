<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'userId'    => $this->user_id,
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'document'  => $this->document,
            'birthDate' => $this->birth_date?->toDateString(),
            'notes'     => $this->notes,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}