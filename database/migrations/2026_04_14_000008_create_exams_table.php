<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete(); // Null if common exam
            $table->string('name'); // e.g. "First Assessment", "Final Exam - 2081"
            $table->string('type'); // assessment, pre-board, final
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'results_published'])->default('upcoming');
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Pivot to link programs/semesters to an exam.
        Schema::create('exam_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('semester');
            $table->timestamps();
            
            $table->unique(['exam_id', 'program_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_program');
        Schema::dropIfExists('exams');
    }
};
