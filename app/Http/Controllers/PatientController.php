<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // Lista pacientes com paginação e busca opcional
    public function index(Request $request)
    {
        $query = Patient::query();

        // busca opcional por nome/email/document
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        $user = $request->user();
        if (
            $user->hasRole('doctor')
            && ! $user->hasRole('admin')
            && ! $user->hasRole('secretary')
        ) {
            $patientIds = \App\Models\Appointment::where('doctor_id', $user->id)
                ->pluck('patient_id')
                ->unique();
            $query->whereIn('id', $patientIds);
        }

        $limit = min((int) $request->input('limit', 10), 100);
        $patients = $query->orderBy('name')->paginate($limit);

        return response()->json([
            'data' => PatientResource::collection($patients),
            'pagination' => [
                'page'       => $patients->currentPage(),
                'limit'      => $patients->perPage(),
                'total'      => $patients->total(),
                'totalPages' => $patients->lastPage(),
                'hasNext'    => $patients->hasMorePages(),
                'hasPrev'    => $patients->currentPage() > 1,
            ],
        ]);
    }

    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());
        return (new PatientResource($patient))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Patient $patient)
    {
        return new PatientResource($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());
        return new PatientResource($patient);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(null, 204);
    }
}