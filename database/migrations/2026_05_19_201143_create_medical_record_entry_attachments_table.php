<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_record_entry_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medical_record_entry_id')
                ->constrained('medical_record_entries')
                ->cascadeOnDelete();

    
            $table->string('file_path');

            $table->string('original_name');

            $table->string('mime', 100);

            $table->unsignedInteger('size');

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('medical_record_entry_id');
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_entry_attachments');
    }
};
