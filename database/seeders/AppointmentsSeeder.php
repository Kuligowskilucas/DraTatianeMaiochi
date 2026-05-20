<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentsSeeder extends Seeder
{
    public function run(): void
    {
        $helena  = User::where('email', 'helena@teste')->first();
        $bruno   = User::where('email', 'bruno@teste')->first();
        $carla   = User::where('email', 'carla@teste')->first();

        $ana      = Patient::whereHas('user', fn ($q) => $q->where('email', 'ana@teste'))->first();
        $marcos   = Patient::whereHas('user', fn ($q) => $q->where('email', 'marcos@teste'))->first();
        $beatriz  = Patient::whereHas('user', fn ($q) => $q->where('email', 'beatriz@teste'))->first();
        $roberto  = Patient::where('email', 'roberto.fernandes@email.com')->first();
        $juliana  = Patient::where('email', 'juliana.pereira@email.com')->first();

        $appointments = [
            // ── Passado (DONE) ──────────────────────────────────────────
            [
                'patient_id'       => $ana->id,
                'doctor_id'        => $helena->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->subWeeks(6)->setTime(9, 0),
                'duration_minutes' => 50,
                'status'           => 'DONE',
                'location'         => 'Consultório 1',
                'notes'            => 'Primeira consulta — anamnese.',
            ],
            [
                'patient_id'       => $ana->id,
                'doctor_id'        => $helena->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->subWeeks(2)->setTime(9, 0),
                'duration_minutes' => 50,
                'status'           => 'DONE',
                'location'         => 'Consultório 1',
                'notes'            => 'Retorno — avaliação de resposta ao ISRS.',
            ],
            [
                'patient_id'       => $marcos->id,
                'doctor_id'        => $helena->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->subWeeks(5)->setTime(10, 0),
                'duration_minutes' => 50,
                'status'           => 'DONE',
                'location'         => 'Consultório 1',
                'notes'            => 'Primeira consulta.',
            ],
            [
                'patient_id'       => $beatriz->id,
                'doctor_id'        => $bruno->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->subWeeks(4)->setTime(14, 0),
                'duration_minutes' => 50,
                'status'           => 'DONE',
                'location'         => 'Consultório 2',
            ],
            [
                'patient_id'       => $roberto->id,
                'doctor_id'        => $helena->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->subWeeks(3)->setTime(11, 0),
                'duration_minutes' => 50,
                'status'           => 'DONE',
                'location'         => 'Consultório 1',
            ],
            [
                'patient_id'       => $beatriz->id,
                'doctor_id'        => $bruno->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->subWeeks(1)->setTime(14, 0),
                'duration_minutes' => 50,
                'status'           => 'DONE',
                'location'         => 'Consultório 2',
                'notes'            => 'Retorno — ajuste de dose.',
            ],

            // ── Hoje (SCHEDULED / CONFIRMED) ────────────────────────────
            [
                'patient_id'       => $marcos->id,
                'doctor_id'        => $helena->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::today()->setTime(9, 0),
                'duration_minutes' => 50,
                'status'           => 'CONFIRMED',
                'location'         => 'Consultório 1',
                'notes'            => 'Retorno — avaliação de resposta ao tratamento.',
            ],
            [
                'patient_id'       => $ana->id,
                'doctor_id'        => $helena->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::today()->setTime(10, 0),
                'duration_minutes' => 50,
                'status'           => 'SCHEDULED',
                'location'         => 'Consultório 1',
            ],
            [
                'patient_id'       => $juliana->id,
                'doctor_id'        => $bruno->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::today()->setTime(14, 0),
                'duration_minutes' => 50,
                'status'           => 'SCHEDULED',
                'location'         => 'Consultório 2',
                'notes'            => 'Primeira consulta.',
            ],

            // ── Futuro (SCHEDULED) ──────────────────────────────────────
            [
                'patient_id'       => $roberto->id,
                'doctor_id'        => $helena->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->addWeeks(1)->setTime(11, 0),
                'duration_minutes' => 50,
                'status'           => 'SCHEDULED',
                'location'         => 'Consultório 1',
            ],
            [
                'patient_id'       => $beatriz->id,
                'doctor_id'        => $bruno->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->addWeeks(2)->setTime(14, 0),
                'duration_minutes' => 50,
                'status'           => 'SCHEDULED',
                'location'         => 'Consultório 2',
            ],
            [
                'patient_id'       => $ana->id,
                'doctor_id'        => $helena->id,
                'created_by'       => $carla->id,
                'starts_at'        => Carbon::now()->addWeeks(3)->setTime(9, 0),
                'duration_minutes' => 50,
                'status'           => 'SCHEDULED',
                'location'         => 'Consultório 1',
            ],
        ];

        foreach ($appointments as $data) {
            Appointment::firstOrCreate(
                [
                    'patient_id' => $data['patient_id'],
                    'doctor_id'  => $data['doctor_id'],
                    'starts_at'  => $data['starts_at'],
                ],
                $data
            );
        }
    }
}