<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\MedicalRecordEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalRecordEntry>
 */
class MedicalRecordEntryFactory extends Factory
{
    protected $model = MedicalRecordEntry::class;

    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'author_id'         => User::factory(),
            'appointment_id'    => null,
            'entry_type'        => 'CONSULTATION',
            'subjective'        => fake()->sentence(),
            'objective'         => null,
            'assessment'        => null,
            'plan'              => null,
            'confidential_notes'=> null,
        ];
    }
}