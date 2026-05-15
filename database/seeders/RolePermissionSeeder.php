<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perms = [
            'patients.view', 'patients.create', 'patients.update', 'patients.delete',
            'appointments.view', 'appointments.create', 'appointments.update', 'appointments.delete',
            'history.view', 'history.create',
            'users.view', 'users.create', 'users.update', 'users.delete',
        ];
        foreach ($perms as $p) Permission::firstOrCreate(['name' => $p]);

        $admin     = Role::firstOrCreate(['name' => 'admin']);
        $secretary = Role::firstOrCreate(['name' => 'secretary']);
        $doctor    = Role::firstOrCreate(['name' => 'doctor']);     // ← novo
        $patient   = Role::firstOrCreate(['name' => 'patient']);

        $admin->givePermissionTo(Permission::all());

        $secretary->givePermissionTo([
            'patients.view', 'patients.create', 'patients.update',
            'appointments.view', 'appointments.create', 'appointments.update',
        ]);

        // Doctor: visão clínica, sem gestão administrativa            // ← novo
        $doctor->givePermissionTo([
            'patients.view',
            'appointments.view',
            'appointments.update',
            'history.view',
            'history.create',
        ]);

        // Paciente: controlado por Policy (somente "próprias")
    }
}