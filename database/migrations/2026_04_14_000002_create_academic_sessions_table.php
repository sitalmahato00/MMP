<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // e.g. "2081-2082" or "2025-2026"
            $table->string('name_bs')->nullable();        // Bikram Sambat name
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false); // Only ONE active at a time
            $table->enum('status', ['upcoming', 'active', 'ended'])->default('upcoming');
            $table->boolean('is_locked')->default(false); // Lock academic data on end
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_sessions');
    }
};
