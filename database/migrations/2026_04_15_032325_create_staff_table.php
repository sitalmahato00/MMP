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
            $table->string('staff_code')->nullable()->unique();
            // Optionally link to users table if they ever get login access
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Public display details
            $table->string('name');
            $table->string('designation')->nullable(); // e.g., 'Accountant', 'Librarian'
            $table->string('department')->nullable();  // e.g., 'Administration', 'Library'
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('employment_status')->default('active');
            $table->date('join_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('salary_amount', 12, 2)->nullable();
            $table->json('working_schedule')->nullable();
            $table->json('assigned_roles')->nullable();
            $table->json('responsibilities')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('public_visible')->default(false);
            $table->boolean('featured')->default(false);
            $table->boolean('show_email_public')->default(false);
            $table->boolean('show_phone_public')->default(false);
            $table->string('photo')->nullable(); // Profile picture
            $table->integer('order')->default(0); // Display order on public page
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });

        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('label');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->date('issued_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'document_type']);
        });

        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status')->default('present');
            $table->string('check_in')->nullable();
            $table->string('check_out')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
        Schema::dropIfExists('staff_documents');
        Schema::dropIfExists('staff');
    }
};
