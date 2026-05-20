<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id'       => Patient::factory(),
            'created_by'       => User::factory(),
            'doctor_id'        => User::factory(),
            'starts_at'        => now()->addDays(fake()->numberBetween(1, 30))->setTime(10, 0),
            'duration_minutes' => 50,
            'status'           => 'SCHEDULED',
            'location'         => 'Consultório 1',
        ];
    }
}