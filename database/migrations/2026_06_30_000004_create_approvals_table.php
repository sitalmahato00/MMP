<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->enum('status', ['pending', 'recommended', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->string('signature')->nullable();
            $table->date('date_bs');
            $table->time('time');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
