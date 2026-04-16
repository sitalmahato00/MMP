<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path');
            $table->string('file_type'); // image, video, document
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // Size in bytes
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete(); // General if null
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
