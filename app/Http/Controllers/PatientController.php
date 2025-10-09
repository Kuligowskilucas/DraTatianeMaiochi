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
}
