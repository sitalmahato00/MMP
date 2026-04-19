<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Programs table (e.g. Diploma in Information Technology - DIT)
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coordinator_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('name');                         // e.g. "Diploma in Information Technology"
            $table->string('code')->unique();               // e.g. "DIT"
            $table->string('ctevt_code', 50)->nullable();
            $table->string('affiliation_type', 50)->default('CTEVT');
            $table->string('slug')->unique();
            $table->unsignedTinyInteger('total_semesters')->default(6); // CTEVT: typically 6 or 8
            $table->unsignedTinyInteger('duration_years')->default(3);
            $table->text('description')->nullable();
            $table->text('eligibility')->nullable();
            $table->string('syllabus')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Subjects table — CTEVT format with Theory + Practical split
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('semester');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type')->default('theory');      // theory, practical, both
            // Full marks structure — CTEVT standard
            $table->unsignedSmallInteger('full_marks_internal_theory')->default(20);
            $table->unsignedSmallInteger('full_marks_external_theory')->default(80);
            $table->unsignedSmallInteger('pass_marks_internal_theory')->default(8);
            $table->unsignedSmallInteger('pass_marks_external_theory')->default(32);
            $table->unsignedSmallInteger('full_marks_internal_practical')->default(30);
            $table->unsignedSmallInteger('full_marks_external_practical')->default(20);
            $table->unsignedSmallInteger('pass_marks_internal_practical')->default(15);
            $table->unsignedSmallInteger('pass_marks_external_practical')->default(10);
            $table->unsignedSmallInteger('credit_hours')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('programs');
    }
};
