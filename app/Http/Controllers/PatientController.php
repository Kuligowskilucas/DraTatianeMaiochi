<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest; 
use App\Models\Patient;

class PatientController extends Controller
{
    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());
        return response()->json($patient, 201);
    }

    public function show(Patient $patient)
    {
        return response()->json($patient);
    }

    public function update(StorePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());
        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(null, 204);
    }
}
