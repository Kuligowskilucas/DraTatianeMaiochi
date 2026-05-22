<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class, 
            AdminUserSeeder::class,
        ]);
    
        if (app()->environment('local', 'staging')) {
            $this->call([
                DoctorUserSeeder::class,
                SecretaryUserSeeder::class,
                PatientsSeeder::class,
                AppointmentsSeeder::class,
                MedicalRecordsSeeder::class,
            ]);
        }
    }
}