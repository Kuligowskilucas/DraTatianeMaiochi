<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('medical_histories');
    }

    public function down(): void
    {
        // Recriação manual se necessário — a estrutura original foi
        // substituída por medical_records + medical_record_entries.
    }
};