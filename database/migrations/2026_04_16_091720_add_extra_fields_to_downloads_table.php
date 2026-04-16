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
        Schema::table('downloads', function (Blueprint $table) {
            $table->text('description')->nullable()->after('file_path');
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('file_type', 20)->nullable()->after('file_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            $table->boolean('is_public')->default(true)->after('department_id');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete()->after('is_public');
        });
    }

    public function down(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['description', 'file_name', 'file_type', 'file_size', 'is_public', 'uploaded_by']);
        });
    }
};
