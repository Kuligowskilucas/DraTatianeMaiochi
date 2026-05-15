<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'       => ['sometimes','required','exists:patients,id'],
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
            'starts_at'        => ['sometimes','required','date'],
            'duration_minutes' => ['sometimes','nullable','integer','min:15','max:240'],
            'location'         => ['sometimes','nullable','string','max:255'],
            'notes'            => ['sometimes','nullable','string'],
            'status'           => ['sometimes','required','in:scheduled,confirmed,in_progress,completed,cancelled,no_show'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge(['status' => strtoupper($this->input('status'))]);
        }
    }
}