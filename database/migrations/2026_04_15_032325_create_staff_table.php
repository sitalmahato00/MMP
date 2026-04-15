<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            // Optionally link to users table if they ever get login access
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Public display details
            $table->string('name');
            $table->string('designation')->nullable(); // e.g., 'Accountant', 'Librarian'
            $table->string('department')->nullable();  // e.g., 'Administration', 'Library'
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable(); // Profile picture
            $table->integer('order')->default(0); // Display order on public page
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
