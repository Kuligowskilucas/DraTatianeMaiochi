<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientsSeeder extends Seeder
{
    public function run(): void
    {
        $linked = [
            [
                'name'       => 'Ana Carolina Souza',
                'email'      => 'ana@teste',
                'password'   => 'patient123',
                'phone'      => '(41) 98801-1234',
                'birth_date' => '1992-03-15',
                'document'   => '123.456.789-00',
            ],
            [
                'name'       => 'Marcos Oliveira',
                'email'      => 'marcos@teste',
                'password'   => 'patient123',
                'phone'      => '(41) 99702-5678',
                'birth_date' => '1985-07-22',
                'document'   => '987.654.321-00',
            ],
            [
                'name'       => 'Beatriz Lima',
                'email'      => 'beatriz@teste',
                'password'   => 'patient123',
                'phone'      => '(41) 98603-9012',
                'birth_date' => '1998-11-08',
                'document'   => '456.123.789-00',
            ],
        ];

        foreach ($linked as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => $data['password'],
                    'is_active' => true,
                ]
            );

            $user->markEmailAsVerified();

            if (! $user->hasRole('patient')) {
                $user->assignRole('patient');
            }

            Patient::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name'       => $data['name'],
                    'email'      => $data['email'],
                    'phone'      => $data['phone'],
                    'birth_date' => $data['birth_date'],
                    'document'   => $data['document'],
                ]
            );
        }

        $clinicOnly = [
            [
                'name'       => 'Roberto Fernandes',
                'email'      => 'roberto.fernandes@email.com',
                'phone'      => '(41) 97704-3456',
                'birth_date' => '1975-05-30',
                'document'   => '321.654.987-00',
            ],
            [
                'name'       => 'Juliana Pereira',
                'email'      => 'juliana.pereira@email.com',
                'phone'      => '(41) 98805-7890',
                'birth_date' => '2001-01-18',
                'document'   => '654.321.123-00',
            ],
        ];

        foreach ($clinicOnly as $data) {
            Patient::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'       => $data['name'],
                    'phone'      => $data['phone'],
                    'birth_date' => $data['birth_date'],
                    'document'   => $data['document'],
                    'user_id'    => null,
                ]
            );
        }
    }
}