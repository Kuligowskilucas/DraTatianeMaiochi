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

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $secretary = Role::firstOrCreate(['name' => 'secretary']);
        $patient = Role::firstOrCreate(['name' => 'patient']);

        $admin->givePermissionTo(Permission::all());

        $secretary->givePermissionTo([
            'patients.view','patients.create','patients.update',
            'appointments.view','appointments.create','appointments.update'
        ]);

        // Paciente não precisa permissão geral; controlamos por Policy (somente “próprias”)
    }
}
