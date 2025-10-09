<?php

namespace App\Http\Requests\Patient;

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Se usa Spatie Permission, você pode checar permissão aqui:
        // return $this->user()->can('patients.create');
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required','string','max:255'],
            'email'      => ['nullable','email','max:255','unique:patients,email'],
            'phone'      => ['nullable','string','max:30'],
            'birth_date' => ['nullable','date'],
            'document'   => ['nullable','string','max:30','unique:patients,document'],
            'notes'      => ['nullable','string'],
            'user_id'    => ['nullable','exists:users,id'],
        ];
    }
}
