<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'patientId'       => $this->patient_id,
            'patientName'     => $this->whenLoaded('patient', fn() => $this->patient?->name),
            'doctorId'        => $this->doctor_id,
            'doctorName'      => $this->whenLoaded('doctor', fn() => $this->doctor?->name),
            'createdBy'       => $this->created_by,
            'startsAt'        => $this->starts_at?->toISOString(),
            'durationMinutes' => $this->duration_minutes,
            'status'          => strtolower($this->status ?? 'scheduled'),
            'location'        => $this->location,
            'notes'           => $this->notes,
            'createdAt'       => $this->created_at?->toISOString(),
            'updatedAt'       => $this->updated_at?->toISOString(),
        ];
    }
}