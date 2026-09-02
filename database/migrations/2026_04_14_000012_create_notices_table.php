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

            // Website Popup Modal fields
            $table->boolean('is_popup')->default(false);
            $table->string('popup_from_bs', 20)->nullable();
            $table->string('popup_to_bs', 20)->nullable();
            $table->date('popup_from')->nullable();
            $table->date('popup_to')->nullable();

            // Department -> Main Site & Popup Request fields
            $table->boolean('main_site_requested')->default(false);
            $table->string('main_site_status', 30)->nullable(); // 'pending', 'approved', 'rejected'
            $table->boolean('request_as_popup')->default(false);
            $table->text('request_note')->nullable();
            $table->timestamp('main_site_approved_at')->nullable();
            $table->foreignId('main_site_approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['is_published', 'type'], 'idx_notices_published_type');
            $table->index(['department_id', 'is_published'], 'idx_notices_dept_published');
            $table->index(['is_popup', 'is_published'], 'idx_notices_popup_published');
            $table->index(['main_site_requested', 'main_site_status'], 'idx_notices_main_site_request');
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
