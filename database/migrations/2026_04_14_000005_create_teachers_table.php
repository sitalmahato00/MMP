<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('employee_id')->unique()->nullable();
            $table->string('designation')->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->date('join_date')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'part-time'])->default('permanent');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->string('section')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'subject_id', 'academic_session_id', 'section'], 'subject_teacher_unique');
            // Performance index
            $table->index(['academic_session_id', 'teacher_id'], 'idx_subteach_session_teacher');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_teacher');
        Schema::dropIfExists('teachers');
    }
};
