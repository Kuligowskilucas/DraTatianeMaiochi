<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalRecord\StoreMedicalRecordEntryRequest;
use App\Http\Requests\MedicalRecord\UpdateMedicalRecordEntryRequest;
use App\Http\Resources\MedicalRecordEntryResource;
use App\Models\MedicalRecordEntry;
use App\Models\Patient;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MedicalRecordEntryController extends Controller
{
    use AuthorizesRequests;

    /**
     * GET /api/patients/{patient}/medical-record/entries
     * Lista paginada de entries do prontuário do paciente (timeline).
     */
    public function index(Patient $patient, Request $request)
    {
        $this->authorize('viewAnyForPatient', [MedicalRecordEntry::class, $patient]);

        // Garante que o record existe (lazy create) pra evitar 404 em paciente novo
        $record = $patient->medicalRecord()->firstOrCreate([]);

        $limit = min((int) $request->input('limit', 20), 100);
        $items = $record->entries()
            ->with(['author', 'appointment'])
            ->paginate($limit);

        return response()->json([
            'data' => MedicalRecordEntryResource::collection($items),
            'pagination' => [
                'page'       => $items->currentPage(),
                'limit'      => $items->perPage(),
                'total'      => $items->total(),
                'totalPages' => $items->lastPage(),
                'hasNext'    => $items->hasMorePages(),
                'hasPrev'    => $items->currentPage() > 1,
            ],
        ]);
    }

    /**
     * POST /api/medical-record-entries
     * Cria entry. Lazy-cria o MedicalRecord se for a primeira entry do paciente.
     */
    public function store(StoreMedicalRecordEntryRequest $request)
    {
        $data = $request->validated();
        $patient = Patient::findOrFail($data['patient_id']);

        $this->authorize('createForPatient', [MedicalRecordEntry::class, $patient]);

        // Valida que o appointment_id (se informado) pertence a esse paciente
        if (! empty($data['appointment_id'])) {
            $belongs = $patient->appointments()
                ->where('id', $data['appointment_id'])
                ->exists();
            if (! $belongs) {
                return response()->json([
                    'message' => 'A consulta informada não pertence a esse paciente.',
                    'errors'  => ['appointment_id' => ['Consulta não pertence ao paciente.']],
                ], 422);
            }
        }

        $record = $patient->medicalRecord()->firstOrCreate([]);

        $entry = $record->entries()->create([
            'author_id'          => $request->user()->id,
            'appointment_id'     => $data['appointment_id'] ?? null,
            'entry_type'         => $data['entry_type'],
            'subjective'         => $data['subjective'] ?? null,
            'objective'          => $data['objective'] ?? null,
            'assessment'         => $data['assessment'] ?? null,
            'plan'               => $data['plan'] ?? null,
            'confidential_notes' => $data['confidential_notes'] ?? null,
        ]);

        $entry->load(['author', 'appointment']);

        return (new MedicalRecordEntryResource($entry))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/medical-record-entries/{entry}
     */
    public function show(MedicalRecordEntry $entry)
    {
        $this->authorize('view', $entry);
        $entry->load(['author', 'appointment']);
        return new MedicalRecordEntryResource($entry);
    }

    /**
     * PUT /api/medical-record-entries/{entry}
     */
    public function update(UpdateMedicalRecordEntryRequest $request, MedicalRecordEntry $entry)
    {
        $this->authorize('update', $entry);
        $entry->update($request->validated());
        $entry->load(['author', 'appointment']);
        return new MedicalRecordEntryResource($entry);
    }

    /**
     * DELETE /api/medical-record-entries/{entry}
     * Soft delete (SoftDeletes trait no model).
     */
    public function destroy(MedicalRecordEntry $entry)
    {
        $this->authorize('delete', $entry);
        $entry->delete();
        return response()->json(null, 204);
    }

    /**
     * GET /api/medical-record-entries/{entry}/activity
     * Histórico de alterações de uma entry.
     */
    public function activity(MedicalRecordEntry $entry)
    {
        $this->authorize('view', $entry);

        $activities = $entry->activities()
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $activities->map(fn ($a) => [
                'id'          => $a->id,
                'event'       => $a->description,                
                'causerId'    => $a->causer_id,
                'causerName'  => $a->causer?->name,
                'changes'     => [
                    'attributes' => $a->properties->get('attributes', []),
                    'old'        => $a->properties->get('old', []),
                ],
                'createdAt'   => $a->created_at?->toISOString(),
            ]),
        ]);
    }

    /**
     * GET /api/me/medical-record/entries
     * Timeline do próprio paciente.
     */
    public function myEntries(Request $request)
    {
        $patient = Patient::where('user_id', $request->user()->id)->firstOrFail();
        return $this->index($patient, $request);
    }
}