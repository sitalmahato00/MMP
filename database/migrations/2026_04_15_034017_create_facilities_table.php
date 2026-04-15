<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // e.g. classroom, lab, workshop, library, transport
            
            // Optional relationships
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            
            $table->text('description')->nullable();
            $table->longText('content')->nullable(); // Detailed rich text
            
            // Multiple resources JSON arrays
            $table->json('images')->nullable(); 
            $table->json('documents')->nullable(); // PDFs etc.
            $table->json('videos')->nullable(); // URLs or paths
            
            $table->integer('capacity')->nullable();
            $table->string('location')->nullable(); // Building, Room Number
            
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
