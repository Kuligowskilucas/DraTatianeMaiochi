<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MedicalRecordController extends Controller
{
    use AuthorizesRequests;

    /**
     * GET /api/patients/{patient}/medical-record
     * Retorna o prontuário do paciente. Cria lazy se ainda não existir.
     */
    public function show(Patient $patient)
    {
        $this->authorize('viewForPatient', [MedicalRecord::class, $patient]);

        $record = $patient->medicalRecord()->firstOrCreate([]);
        $record->load('patient');

        return new MedicalRecordResource($record);
    }
}