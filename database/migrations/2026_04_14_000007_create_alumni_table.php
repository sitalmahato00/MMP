<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alumni Table (Auto-migrated from Students upon session end)
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete(); // The original student record (archived)
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('graduation_year', 4); // Based on passing academic session
            $table->string('current_job')->nullable();
            $table->string('company_name')->nullable();
            $table->text('achievements')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(true); // Auto-created are verified
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
