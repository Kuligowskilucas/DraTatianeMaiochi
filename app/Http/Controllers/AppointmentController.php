<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        if ($patientId = $request->input('patientId')) {
            $query->where('patient_id', $patientId);
        }
        if ($doctorId = $request->input('doctorId')) {
            $query->where('doctor_id', $doctorId);
        }
        if ($date = $request->input('date')) {
            $query->whereDate('starts_at', $date);
        }
        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        $user = $request->user();
        if (
            $user->hasRole('doctor')
            && ! $user->hasRole('admin')
            && ! $user->hasRole('secretary')
        ) {
            $query->where('doctor_id', $user->id);
        }

        $limit = min((int) $request->input('limit', 10), 100);
        $items = $query->orderBy('starts_at', 'desc')->paginate($limit);

        return response()->json([
            'data' => AppointmentResource::collection($items),
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

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor']);
        return new AppointmentResource($appointment);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['status'] = 'SCHEDULED';

        if ($conflict = $this->hasConflict($data['doctor_id'] ?? null, $data['starts_at'], $data['duration_minutes'] ?? 50)) {
            return response()->json(['message' => 'Conflito de agenda para o médico.'], 422);
        }

        $appointment = Appointment::create($data);
        $appointment->load(['patient', 'doctor']);

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $data = $request->validated();

        if ((isset($data['doctor_id']) || isset($data['starts_at'])) && ($data['status'] ?? null) !== 'CANCELLED') {
            $doctorId = $data['doctor_id'] ?? $appointment->doctor_id;
            $startsAt = $data['starts_at'] ?? $appointment->starts_at;
            $duration = $data['duration_minutes'] ?? $appointment->duration_minutes ?? 50;

            if ($this->hasConflict($doctorId, $startsAt, $duration, $appointment->id)) {
                return response()->json(['message' => 'Conflito de agenda para o médico.'], 422);
            }
        }

        $appointment->update($data);
        $appointment->load(['patient', 'doctor']);

        return new AppointmentResource($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(null, 204);
    }

    public function cancel(Appointment $appointment)
    {
        if ($appointment->status === 'CANCELLED') {
            return response()->json(['message' => 'Consulta já está cancelada.'], 422);
        }
        $appointment->update(['status' => 'CANCELLED']);
        $appointment->load(['patient', 'doctor']);
        return new AppointmentResource($appointment);
    }

    public function confirm(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        abort_if(
            !$user->hasRole('patient') || $appointment->patient?->user_id !== $user->id,
            403,
            'Apenas o paciente dono pode confirmar.'
        );

        if ($appointment->status === 'CANCELLED') {
            return response()->json(['message' => 'Consulta cancelada não pode ser confirmada.'], 422);
        }

        $appointment->update(['status' => 'CONFIRMED']);
        $appointment->load(['patient', 'doctor']);

        return new AppointmentResource($appointment);
    }

    public function myAppointments(Request $request)
    {
        $patient = Patient::where('user_id', $request->user()->id)->firstOrFail();

        $items = Appointment::with(['patient', 'doctor'])
            ->where('patient_id', $patient->id)
            ->orderBy('starts_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => AppointmentResource::collection($items),
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

    private function hasConflict($doctorId, $startsAt, $duration, $excludeId = null): bool
    {
        if (!$doctorId) return false;

        $query = Appointment::where('doctor_id', $doctorId)
            ->where('status', '!=', 'CANCELLED')
            ->whereBetween('starts_at', [
                Carbon::parse($startsAt)->subMinutes($duration),
                Carbon::parse($startsAt)->addMinutes($duration),
            ]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}