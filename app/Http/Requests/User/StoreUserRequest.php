<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Rota já protegida por middleware role:admin
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'roles'     => ['required', 'array', 'min:1'],
            'roles.*'   => ['string', 'in:admin,secretary,patient,doctor'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required'     => 'Selecione ao menos uma role.',
            'roles.min'          => 'Selecione ao menos uma role.',
            'roles.*.in'         => 'Role inválida. Use admin, secretary, doctor ou patient.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'email.unique' => 'Este e-mail já está em uso.',
        ];
    }
}