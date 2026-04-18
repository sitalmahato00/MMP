<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_session_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'running', 'delayed', 'completed'])->default('upcoming');
            $table->enum('delay_reason', ['exam_late', 'holidays', 'internal_delay', 'admin_decision'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['academic_session_id', 'semester_number'], 'uniq_session_semester_number');
            $table->index(['academic_session_id', 'status'], 'idx_session_semester_status');
            $table->index(['academic_session_id', 'is_active'], 'idx_session_semester_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_session_semesters');
    }
};
