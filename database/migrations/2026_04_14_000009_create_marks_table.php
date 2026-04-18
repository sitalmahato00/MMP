<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('semester');

            // CTEVT Marks Structure
            $table->decimal('internal_theory_marks', 5, 2)->nullable();
            $table->decimal('external_theory_marks', 5, 2)->nullable();
            $table->decimal('internal_practical_marks', 5, 2)->nullable();
            $table->decimal('external_practical_marks', 5, 2)->nullable();

            $table->boolean('is_absent')->default(false);
            $table->boolean('is_withheld')->default(false);

            // Marks flow: Teacher -> HOD -> Principal -> Published
            $table->enum('status', ['draft', 'submitted', 'approved', 'published'])->default('draft');

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id', 'subject_id']);
            // Performance indexes
            $table->index(['exam_id', 'status'], 'idx_marks_exam_status');
            $table->index(['student_id', 'status'], 'idx_marks_student_status');
            $table->index(['program_id', 'semester'], 'idx_marks_program_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
