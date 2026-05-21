<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

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
        $data = $request->validated();

        $tempPassword = null;

        $patient = DB::transaction(function () use ($request, $data, &$tempPassword) {
            $patient = null;

            if (
                $request->user()?->hasRole('secretary')
                && blank($data['user_id'] ?? null)
                && ! blank($data['email'] ?? null)
            ) {
                $tempPassword = Str::random(12);

                $user = User::withTrashed()->firstWhere('email', $data['email']);

                if (! $user) {
                    $user = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => $tempPassword,
                        'is_active' => true,
                        'must_change_password' => true,
                    ]);
                } else {
                    $user->forceFill([
                        'name' => $data['name'],
                        'password' => $tempPassword,
                        'is_active' => true,
                        'must_change_password' => true,
                    ])->save();

                    if ($user->trashed()) {
                        $user->restore();
                    }
                }

                if (! $user->hasRole('patient')) {
                    $user->assignRole('patient');
                }

                $data['user_id'] = $user->id;
            }

            if (! blank($data['document'] ?? null)) {
                $patient = Patient::withTrashed()
                    ->where('document', $data['document'])
                    ->first();
            }

            if (! $patient && ! blank($data['email'] ?? null)) {
                $patient = Patient::withTrashed()
                    ->where('email', $data['email'])
                    ->first();
            }

            if ($patient) {
                $patient->restore();
                $patient->fill($data);
                $patient->save();

                return $patient;
            }

            return Patient::create($data);
        });

        $response = (new PatientResource($patient))->response()->setStatusCode(201);
        // if provisioning provided a temp password, include it in top-level meta
        if (! empty($tempPassword)) {
            $response->setData(array_merge($response->getData(true), [
                'tempPassword' => $tempPassword,
            ]));
        }

        return $response;
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