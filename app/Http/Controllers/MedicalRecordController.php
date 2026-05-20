<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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
    
        return (new MedicalRecordResource($record))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * GET /api/me/medical-record
     * Paciente vê o próprio prontuário sem precisar saber o próprio patient_id.
     */
    public function myRecord(Request $request)
    {
        $patient = Patient::where('user_id', $request->user()->id)->firstOrFail();
        return $this->show($patient);
    }
}