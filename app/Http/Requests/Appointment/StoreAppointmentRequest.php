<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class StoreAppointmentRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->can('appointments.create'); }
    public function rules(): array {
        return [
            'patient_id'       => ['required','exists:patients,id'],
            'doctor_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value === null) return;
                    $user = User::find($value);
                    if (! $user || ! $user->hasRole('doctor')) {
                        $fail('O médico selecionado não tem a função de médico no sistema.');
                    }
                },
            ],
            'starts_at'        => ['required','date','after:now'],
            'duration_minutes' => ['nullable','integer','min:15','max:240'],
            'location'         => ['nullable','string','max:255'],
            'notes'            => ['nullable','string'],
        ];
    }
}




