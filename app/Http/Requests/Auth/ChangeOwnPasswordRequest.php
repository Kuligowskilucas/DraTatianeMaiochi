<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangeOwnPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'         => 'A senha atual é obrigatória.',
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.required'                 => 'A nova senha é obrigatória.',
            'password.min'                      => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed'                => 'A confirmação da nova senha não confere.',
            'password.different'                => 'A nova senha deve ser diferente da atual.',
        ];
    }
}