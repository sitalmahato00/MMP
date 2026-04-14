<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('attachment')->nullable();

            $table->enum('type', ['general', 'department', 'class', 'teachers', 'exam'])->default('general');
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('semester')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['is_published', 'type'], 'idx_notices_published_type');
            $table->index(['department_id', 'is_published'], 'idx_notices_dept_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
