<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Garante que a role 'doctor' existe (defensivo: independe do seeder)
        Role::firstOrCreate(['name' => 'doctor']);

        // Pega todos os user_ids únicos que aparecem como doctor_id em appointments
        $userIds = DB::table('appointments')
            ->whereNotNull('doctor_id')
            ->distinct()
            ->pluck('doctor_id');

        $promoted = 0;
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && ! $user->hasRole('doctor')) {
                $user->assignRole('doctor');
                $promoted++;
            }
        }

        if ($promoted > 0) {
            echo "  → Promovidos {$promoted} usuário(s) à role 'doctor'.\n";
        }
    }

    public function down(): void
    {
        // Não revertemos: tirar role pode quebrar acesso silenciosamente.
        // Se precisar reverter, faça manualmente via tinker:
        //   User::find($id)->removeRole('doctor');
    }
};