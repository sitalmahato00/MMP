<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctevt_notices', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // 'general' or 'result'
            $table->string('external_id')->nullable()->index(); // ID from CTEVT API
            $table->string('title');
            $table->text('url')->nullable();
            $table->string('updated_date')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('files_count')->default(0);
            $table->json('raw_data')->nullable(); // Store full API response
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['type', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctevt_notices');
    }
};
