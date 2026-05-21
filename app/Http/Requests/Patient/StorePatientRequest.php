<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'email'      => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('patients', 'email')->whereNull('deleted_at'),
            ],
            'phone'      => ['nullable','string','max:30'],
            'birth_date' => ['nullable','date'],
            'document'   => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('patients', 'document')->whereNull('deleted_at'),
            ],
            'notes'      => ['nullable','string'],
            'user_id'    => ['nullable','exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            if (
                $this->user()?->hasRole('secretary')
                && blank($this->input('email'))
                && blank($this->input('user_id'))
            ) {
                $v->errors()->add(
                    'email',
                    'O email é obrigatório para criar paciente com acesso ao portal.'
                );
            }
        });
    }
}
