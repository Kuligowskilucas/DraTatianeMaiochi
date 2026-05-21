<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft-deleted patients can be recreated, so the database cannot keep
     * hard unique constraints on email/document.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['document']);
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->unique('email');
            $table->unique('document');
        });
    }
};
