<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // Mostra detalhes de uma consulta
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient.user','doctor.user','clinic']);
        return response()->json($appointment);
    }

    // Cria uma nova consulta
    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();

         if ($data['doctor_id'] ?? null) {
                $overlap = Appointment::where('doctor_id', $data['doctor_id'])
                ->where('status','!=','CANCELLED')
                ->whereBetween('starts_at', [
                    Carbon::parse($data['starts_at'])->subMinutes($data['duration_minutes'] ?? 50),
                    Carbon::parse($data['starts_at'])->addMinutes($data['duration_minutes'] ?? 50),
                ])->exists();
            if ($overlap) {
                return response()->json(['message'=>'Conflito de agenda para o médico.'], 422);
            }
         }
    }

    // Confirma presença do paciente na consulta
    public function confirm(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        // apenas paciente dono:
        abort_if(!$user->hasRole('patient') || $appointment->patient?->user_id !== $user->id, 403);

        if ($appointment->status === 'CANCELLED') {
            return response()->json(['message'=>'Consulta cancelada não pode ser confirmada.'], 422);
        }
        $appointment->update(['status'=>'CONFIRMED']);
        return response()->json(['message'=>'Presença confirmada.']);
    }

    // Lista consultas do paciente logado 
    public function MyAppointments(Request $request)
    {
        $patient = Patient::where('user_id',$request->user()->id)->firstOrFail();
        $items = Appointment::where('patient_id',$patient->id)
            ->orderBy('starts_at','desc')->paginate(20);
        return response()->json($items);
    }
}
