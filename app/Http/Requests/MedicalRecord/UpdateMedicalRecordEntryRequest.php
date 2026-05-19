<?php

namespace App\Http\Requests\MedicalRecord;

use App\Models\MedicalRecordEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalRecordEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'entry_type'         => ['sometimes', Rule::in(MedicalRecordEntry::TYPES)],
            'appointment_id'     => ['sometimes', 'nullable', 'integer', 'exists:appointments,id'],
            'subjective'         => ['sometimes', 'nullable', 'string'],
            'objective'          => ['sometimes', 'nullable', 'string'],
            'assessment'         => ['sometimes', 'nullable', 'string'],
            'plan'               => ['sometimes', 'nullable', 'string'],
            'confidential_notes' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'entry_type.in'        => 'Tipo inválido. Use ANAMNESIS, CONSULTATION, FOLLOW_UP ou NOTE.',
            'appointment_id.exists' => 'Consulta não encontrada.',
        ];
    }
}