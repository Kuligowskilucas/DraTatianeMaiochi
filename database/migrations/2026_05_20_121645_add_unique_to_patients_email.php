<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * O FormRequest de Patient exige email único, mas a migration original
     * só tinha index. Trazendo o DB pra acordar com a validação.
     *
     * Atenção: se houver pacientes com email duplicado em produção,
     * essa migration vai falhar. Resolva os duplicados antes de rodar
     * em prod (deduplicar por id mais recente ou outro critério).
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->index('email');
        });
    }
};