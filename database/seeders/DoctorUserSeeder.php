<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DoctorUserSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'doctor']);

        $doctors = [
            ['name' => 'Dra. Helena Vargas', 'email' => 'helena@teste'],
            ['name' => 'Dr. Bruno Tavares',  'email' => 'bruno@teste'],
        ];

        foreach ($doctors as $info) {
            $user = User::firstOrCreate(
                ['email' => $info['email']],
                [
                    'name'      => $info['name'],
                    'password'  => 'doctor123',
                    'is_active' => true,
                ]
            );

            $user->markEmailAsVerified();

            if (! $user->hasRole('doctor')) {
                $user->assignRole('doctor');
            }
        }
    }
}