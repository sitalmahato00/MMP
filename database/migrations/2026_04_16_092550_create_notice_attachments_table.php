<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notice_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });

        // Add news/event to notices type enum
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE notices MODIFY COLUMN type ENUM('general','department','class','teachers','exam','news','event') DEFAULT 'general'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_attachments');
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE notices MODIFY COLUMN type ENUM('general','department','class','teachers','exam') DEFAULT 'general'");
        }
    }
};
