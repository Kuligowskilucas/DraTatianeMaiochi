<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // quem agendou
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete(); // psiquiatra
            $table->dateTime('starts_at');
            $table->unsignedInteger('duration_minutes')->default(50);
            $table->enum('status', ['SCHEDULED','CONFIRMED','DONE','CANCELLED'])->default('SCHEDULED');
            $table->string('location')->nullable(); // presencial/online/link
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['doctor_id','starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
