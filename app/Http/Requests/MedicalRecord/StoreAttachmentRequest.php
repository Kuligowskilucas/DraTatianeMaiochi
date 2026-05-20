<?php

namespace App\Http\Requests\MedicalRecord;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'  => 'Selecione um arquivo.',
            'file.file'      => 'O upload é inválido.',
            'file.mimetypes' => 'Formato não permitido. Use PDF, JPG ou PNG.',
            'file.max'       => 'Arquivo excede 10MB.',
        ];
    }
}