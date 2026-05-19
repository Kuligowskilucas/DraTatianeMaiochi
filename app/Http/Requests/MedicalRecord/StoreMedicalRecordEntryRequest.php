<?php

namespace App\Http\Requests\MedicalRecord;

use App\Models\MedicalRecordEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMedicalRecordEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy + middleware fazem authz fina; aqui só exige user autenticado.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'patient_id'         => ['required', 'integer', 'exists:patients,id'],
            'entry_type'         => ['required', Rule::in(MedicalRecordEntry::TYPES)],
            'appointment_id'     => ['nullable', 'integer', 'exists:appointments,id'],
            'subjective'         => ['nullable', 'string'],
            'objective'          => ['nullable', 'string'],
            'assessment'         => ['nullable', 'string'],
            'plan'               => ['nullable', 'string'],
            'confidential_notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        // Exige que pelo menos um campo de conteúdo esteja preenchido.
        // Entry vazia é noop — evita lixo no prontuário.
        $validator->after(function ($v) {
            $fields = ['subjective', 'objective', 'assessment', 'plan', 'confidential_notes'];
            $hasContent = collect($fields)->some(fn ($f) => filled($this->input($f)));

            if (! $hasContent) {
                $v->errors()->add(
                    'subjective',
                    'Preencha ao menos um campo do SOAP ou notas confidenciais.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'patient_id.required'  => 'O paciente é obrigatório.',
            'patient_id.exists'    => 'Paciente não encontrado.',
            'entry_type.required'  => 'O tipo de entrada é obrigatório.',
            'entry_type.in'        => 'Tipo inválido. Use ANAMNESIS, CONSULTATION, FOLLOW_UP ou NOTE.',
            'appointment_id.exists' => 'Consulta não encontrada.',
        ];
    }
}