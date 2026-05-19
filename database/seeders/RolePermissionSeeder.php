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

        Permission::whereIn('name', ['history.view', 'history.create'])->delete();

        $perms = [
            'patients.view', 'patients.create', 'patients.update', 'patients.delete',
            'appointments.view', 'appointments.create', 'appointments.update', 'appointments.delete',
            'medical_records.view', 'medical_records.create',
            'medical_records.update', 'medical_records.delete',
            'users.view', 'users.create', 'users.update', 'users.delete',
        ];
        foreach ($perms as $p) Permission::firstOrCreate(['name' => $p]);

        $admin     = Role::firstOrCreate(['name' => 'admin']);
        $secretary = Role::firstOrCreate(['name' => 'secretary']);
        $doctor    = Role::firstOrCreate(['name' => 'doctor']);
        $patient   = Role::firstOrCreate(['name' => 'patient']);

        $admin->givePermissionTo(Permission::all());

        $secretary->givePermissionTo([
            'patients.view', 'patients.create', 'patients.update',
            'appointments.view', 'appointments.create', 'appointments.update',
        ]);

        $doctor->syncPermissions([
            'patients.view',
            'appointments.view',
            'appointments.update',
            'medical_records.view',
            'medical_records.create',
            'medical_records.update',
        ]);
    }
}