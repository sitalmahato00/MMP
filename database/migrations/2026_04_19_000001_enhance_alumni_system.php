<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to alumni table
        Schema::table('alumni', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('achievements');
            $table->json('skills')->nullable()->after('bio');
            $table->string('linkedin_url')->nullable()->after('skills');
            $table->string('github_url')->nullable()->after('linkedin_url');
            $table->string('portfolio_url')->nullable()->after('github_url');
            $table->string('work_location')->nullable()->after('company_name');
            $table->string('cv_path')->nullable()->after('portfolio_url');
            $table->unsignedTinyInteger('profile_completion')->default(0)->after('cv_path');
            $table->string('visibility')->default('public')->after('profile_completion'); // public, private
            $table->string('employment_status')->default('unknown')->after('company_name'); // employed, studying, unemployed, freelancing, unknown
            $table->string('roll_number')->nullable()->after('program_id');
            $table->string('admission_year', 4)->nullable()->after('roll_number');
        });

        // Alumni Projects (Minor & Major)
        Schema::create('alumni_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->enum('type', ['minor', 'major']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('supervisor')->nullable();
            $table->json('technologies')->nullable();
            $table->json('team_members')->nullable();
            $table->string('report_path')->nullable(); // PDF
            $table->json('screenshots')->nullable(); // Array of image paths
            $table->string('github_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('status', ['in_progress', 'completed'])->default('completed');
            $table->boolean('is_visible')->default(true); // Public visibility
            $table->string('year', 4)->nullable();
            $table->timestamps();

            $table->unique(['alumni_id', 'type']); // One minor + one major per alumnus
        });

        // Alumni Achievements
        Schema::create('alumni_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('year', 4)->nullable();
            $table->timestamps();
        });

        // Alumni Employment History
        Schema::create('alumni_employment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->string('job_title');
            $table->string('company_name');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable(); // null = current
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_employment_history');
        Schema::dropIfExists('alumni_achievements');
        Schema::dropIfExists('alumni_projects');

        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn([
                'bio', 'skills', 'linkedin_url', 'github_url', 'portfolio_url',
                'work_location', 'cv_path', 'profile_completion', 'visibility',
                'employment_status', 'roll_number', 'admission_year',
            ]);
        });
    }
};
