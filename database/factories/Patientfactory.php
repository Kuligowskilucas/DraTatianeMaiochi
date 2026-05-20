<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'name'       => fake()->name(),
            'email'      => fake()->unique()->safeEmail(),
            'phone'      => fake()->phoneNumber(),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
        ];
    }
}