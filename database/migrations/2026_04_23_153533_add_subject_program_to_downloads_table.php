<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->after('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('semester')->nullable()->after('program_id');
            $table->enum('visibility', ['public', 'students', 'private'])->default('students')->after('is_public');
        });
    }

    public function down(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['program_id']);
            $table->dropColumn(['subject_id', 'program_id', 'semester', 'visibility']);
        });
    }
};
