<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SecretaryUserSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'secretary']);

        $user = User::firstOrCreate(
            ['email' => 'carla@teste'],
            [
                'name'      => 'Carla Santos',
                'password'  => 'secretary123',
                'is_active' => true,
            ]
        );

        $user->markEmailAsVerified();

        if (! $user->hasRole('secretary')) {
            $user->assignRole('secretary');
        }
    }
}