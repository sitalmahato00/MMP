<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('semester');
            $table->string('section')->nullable();
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->date('due_date');
            
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            
            $table->text('student_note')->nullable();
            $table->string('attachment')->nullable();
            $table->enum('status', ['submitted', 'graded', 'late'])->default('submitted');
            $table->decimal('marks_obtained', 5, 2)->nullable();
            $table->text('teacher_feedback')->nullable();
            
            $table->timestamps();
            
            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
