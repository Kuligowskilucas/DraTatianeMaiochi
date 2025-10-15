<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest; 
use App\Models\Patient;

class PatientController extends Controller
{
    // Cria um novo paciente
    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());
        return response()->json($patient, 201);
    }

    // Mostra detalhes de um paciente
    public function show(Patient $patient)
    {
        return response()->json($patient);
    }

    // Atualiza dados de um paciente
    public function update(StorePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());
        return response()->json($patient);
    }

    // Deleta um paciente
    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(null, 204);
    }
}
