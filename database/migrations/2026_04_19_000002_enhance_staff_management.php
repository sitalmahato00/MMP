<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('staff_code')->nullable()->unique()->after('id');
            $table->string('address')->nullable()->after('phone');
            $table->date('dob')->nullable()->after('address');
            $table->string('gender')->nullable()->after('dob');
            $table->string('employment_type')->nullable()->after('gender');
            $table->string('employment_status')->default('active')->after('employment_type');
            $table->date('join_date')->nullable()->after('employment_status');
            $table->date('end_date')->nullable()->after('join_date');
            $table->decimal('salary_amount', 12, 2)->nullable()->after('end_date');
            $table->json('working_schedule')->nullable()->after('salary_amount');
            $table->json('assigned_roles')->nullable()->after('working_schedule');
            $table->json('responsibilities')->nullable()->after('assigned_roles');
            $table->text('bio')->nullable()->after('responsibilities');
            $table->boolean('public_visible')->default(false)->after('bio');
            $table->boolean('featured')->default(false)->after('public_visible');
            $table->boolean('show_email_public')->default(false)->after('featured');
            $table->boolean('show_phone_public')->default(false)->after('show_email_public');
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

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn([
                'staff_code', 'address', 'dob', 'gender', 'employment_type', 'employment_status',
                'join_date', 'end_date', 'salary_amount', 'working_schedule', 'assigned_roles',
                'responsibilities', 'bio', 'public_visible', 'featured', 'show_email_public',
                'show_phone_public',
            ]);
        });
    }
};
