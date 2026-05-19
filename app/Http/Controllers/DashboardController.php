<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function overview(Request $request)
    {
        $user        = $request->user();
        $today       = Carbon::today();
        $tomorrow    = Carbon::tomorrow();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now()->endOfWeek();

        // Admin / Secretary — visão de clínica
        if ($user->hasRole('admin') || $user->hasRole('secretary')) {
            $data = [
                'role'                            => $user->hasRole('admin') ? 'admin' : 'secretary',
                'patientsTotal'                   => Patient::count(),
                'appointmentsToday'               => Appointment::whereBetween('starts_at', [$today, $tomorrow])
                    ->where('status', '!=', 'CANCELLED')
                    ->count(),
                'appointmentsThisWeek'            => Appointment::whereBetween('starts_at', [$startOfWeek, $endOfWeek])
                    ->where('status', '!=', 'CANCELLED')
                    ->count(),
                'appointmentsPendingConfirmation' => Appointment::where('status', 'SCHEDULED')
                    ->where('starts_at', '>=', Carbon::now())
                    ->count(),
            ];

            if ($user->hasRole('admin')) {
                $data['usersTotal']  = User::count();
                $data['usersByRole'] = [
                    'admin'     => User::role('admin')->count(),
                    'secretary' => User::role('secretary')->count(),
                    'doctor'    => User::role('doctor')->count(),
                    'patient'   => User::role('patient')->count(),
                ];
            }

            return response()->json(['data' => $data]);
        }

        // Doctor — visão do próprio
        if ($user->hasRole('doctor')) {
            $base = Appointment::where('doctor_id', $user->id);

            return response()->json([
                'data' => [
                    'role'                   => 'doctor',
                    'myAppointmentsToday'    => (clone $base)
                        ->whereBetween('starts_at', [$today, $tomorrow])
                        ->where('status', '!=', 'CANCELLED')
                        ->count(),
                    'myAppointmentsThisWeek' => (clone $base)
                        ->whereBetween('starts_at', [$startOfWeek, $endOfWeek])
                        ->where('status', '!=', 'CANCELLED')
                        ->count(),
                    'myPatientsCount'        => (clone $base)
                        ->distinct('patient_id')
                        ->count('patient_id'),
                ],
            ]);
        }

        // Patient — próxima consulta + contagens próprias
        if ($user->hasRole('patient')) {
            $patient = Patient::where('user_id', $user->id)->first();

            if (! $patient) {
                return response()->json(['data' => ['role' => 'patient']]);
            }

            $base = Appointment::where('patient_id', $patient->id);
            $next = (clone $base)
                ->where('starts_at', '>=', Carbon::now())
                ->where('status', '!=', 'CANCELLED')
                ->orderBy('starts_at', 'asc')
                ->with(['doctor:id,name'])
                ->first();

            return response()->json([
                'data' => [
                    'role'                   => 'patient',
                    'nextAppointment'        => $next ? [
                        'id'         => $next->id,
                        'startsAt'   => $next->starts_at->toISOString(),
                        'doctorName' => $next->doctor?->name,
                        'status'     => strtolower($next->status),
                    ] : null,
                    'myAppointmentsUpcoming' => (clone $base)
                        ->where('starts_at', '>=', Carbon::now())
                        ->where('status', '!=', 'CANCELLED')
                        ->count(),
                    'myAppointmentsPast'     => (clone $base)
                        ->where('starts_at', '<', Carbon::now())
                        ->count(),
                ],
            ]);
        }

        return response()->json(['data' => ['role' => null]]);
    }
}