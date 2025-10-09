<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{

    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255','unique:patients,email,'.$this->patient->id],
            'phone' => ['nullable','string','max:20'],
            'birth_date' => ['nullable','date'],
            'document' => ['nullable','string','max:50'],
        ];
    }
}
