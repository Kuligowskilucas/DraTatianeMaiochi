<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('appointments.create');
    }
    public function rules(): array {
        return [
            'patient_id' => ['required','exists:patients,id'],
            'doctor_id'  => ['nullable','exists:users,id'],
            'starts_at'  => ['required','date','after:now'],
            'duration_minutes' => ['nullable','integer','min:15','max:240'],
            'location'   => ['nullable','string','max:255'],
            'notes'      => ['nullable','string'],
        ];
    }
}

