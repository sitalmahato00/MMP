<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctevt_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->integer('notices_added')->default(0);
            $table->integer('notices_updated')->default(0);
            $table->integer('notices_total')->default(0);
            $table->text('error_message')->nullable();
            $table->string('triggered_by')->default('manual'); // manual, scheduled, api
            $table->string('external_service_ip')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('metadata')->nullable(); // Store additional info
            $table->timestamps();
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctevt_sync_logs');
    }
};
