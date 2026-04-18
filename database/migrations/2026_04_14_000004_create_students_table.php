<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->string('student_no', 50)->nullable()->unique(); // Student ID assigned by admin
            $table->string('registration_number')->nullable();
            $table->unsignedTinyInteger('current_semester')->default(1);
            $table->string('section')->nullable();
            $table->string('batch')->nullable();
            $table->date('admission_date')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'dropped', 'suspended'])->default('active');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Roll number assigned by HOD (not by admin)
            $table->string('roll_number', 20)->nullable();
            // Performance indexes
            $table->index(['department_id', 'academic_session_id'], 'idx_students_dept_session');
            $table->index(['program_id', 'current_semester'], 'idx_students_program_sem');
            $table->index(['status', 'is_archived'], 'idx_students_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
