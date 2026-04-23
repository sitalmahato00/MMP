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

            $table->enum('type', ['general', 'department', 'program', 'teachers', 'exam', 'news', 'event', 'ctevt'])->default('general');
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

        Schema::create('notice_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_attachments');
        Schema::dropIfExists('notices');
    }
};
