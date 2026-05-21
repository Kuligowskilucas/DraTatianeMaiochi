<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('patients', 'email')
                    ->ignore($this->patient->id)
                    ->whereNull('deleted_at'),
            ],
            'phone' => ['nullable','string','max:20'],
            'birth_date' => ['nullable','date'],
            'document' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('patients', 'document')
                    ->ignore($this->patient->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
