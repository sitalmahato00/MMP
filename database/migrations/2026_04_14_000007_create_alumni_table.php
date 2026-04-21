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
            $table->string('roll_number')->nullable();
            $table->string('admission_year', 4)->nullable();
            $table->string('graduation_year', 4); // Based on passing academic session
            $table->date('graduation_date')->nullable(); // Actual graduation date
            $table->string('current_status')->default('recent_graduate'); // recent_graduate, employed, entrepreneur, further_study, unemployed
            $table->string('current_job')->nullable();
            $table->string('company_name')->nullable();
            $table->string('employment_status')->default('unknown'); // employed, studying, unemployed, freelancing, unknown
            $table->string('work_location')->nullable();
            $table->text('achievements')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('cv_path')->nullable();
            $table->unsignedTinyInteger('profile_completion')->default(0);
            $table->string('visibility')->default('public');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true); // Active alumni status
            $table->boolean('is_verified')->default(true); // Auto-created are verified
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('alumni_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->enum('type', ['minor', 'major']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('supervisor')->nullable();
            $table->json('technologies')->nullable();
            $table->json('team_members')->nullable();
            $table->string('report_path')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('github_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('status', ['in_progress', 'completed'])->default('completed');
            $table->boolean('is_visible')->default(true);
            $table->string('year', 4)->nullable();
            $table->timestamps();

            $table->unique(['alumni_id', 'type']);
        });

        Schema::create('alumni_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('year', 4)->nullable();
            $table->timestamps();
        });

        Schema::create('alumni_employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->string('job_title');
            $table->string('company_name');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_employments');
        Schema::dropIfExists('alumni_achievements');
        Schema::dropIfExists('alumni_projects');
        Schema::dropIfExists('alumni');
    }
};
