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
            // Assessment marks (for monthly assessments)
            $table->decimal('assessment_attendance_percent', 5, 2)->nullable();
            $table->decimal('assessment_full_marks', 6, 2)->nullable();
            $table->decimal('assessment_pass_marks', 6, 2)->nullable();
            $table->decimal('assessment_obtained_marks', 6, 2)->nullable();

            // CTEVT validation marks (stored per mark for historical accuracy)
            $table->decimal('ctevt_full_marks_internal_theory', 6, 2)->nullable();
            $table->decimal('ctevt_pass_marks_internal_theory', 6, 2)->nullable();
            $table->decimal('ctevt_full_marks_external_theory', 6, 2)->nullable();
            $table->decimal('ctevt_pass_marks_external_theory', 6, 2)->nullable();
            $table->decimal('ctevt_full_marks_internal_practical', 6, 2)->nullable();
            $table->decimal('ctevt_pass_marks_internal_practical', 6, 2)->nullable();
            $table->decimal('ctevt_full_marks_external_practical', 6, 2)->nullable();
            $table->decimal('ctevt_pass_marks_external_practical', 6, 2)->nullable();

            // Attendance tracking for assessment exams
            $table->date('exam_attendance_date')->nullable();
            $table->boolean('was_present_on_exam_date')->default(true);
            $table->text('attendance_remarks')->nullable();

            $table->boolean('is_absent')->default(false);
            $table->boolean('is_withheld')->default(false);
            $table->boolean('is_delayed')->default(false);
            $table->text('delay_reason')->nullable();

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

        Schema::create('exam_subject_marking_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            $table->decimal('full_marks_internal_theory', 6, 2)->default(0);
            $table->decimal('pass_marks_internal_theory', 6, 2)->default(0);
            $table->decimal('full_marks_external_theory', 6, 2)->default(0);
            $table->decimal('pass_marks_external_theory', 6, 2)->default(0);
            $table->decimal('full_marks_internal_practical', 6, 2)->default(0);
            $table->decimal('pass_marks_internal_practical', 6, 2)->default(0);
            $table->decimal('full_marks_external_practical', 6, 2)->default(0);
            $table->decimal('pass_marks_external_practical', 6, 2)->default(0);

            $table->timestamps();

            $table->unique(['exam_id', 'subject_id'], 'exam_subject_marking_unique');
            $table->index(['exam_id', 'subject_id'], 'idx_exam_subject_marking_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subject_marking_schemes');
        Schema::dropIfExists('marks');
    }
};
