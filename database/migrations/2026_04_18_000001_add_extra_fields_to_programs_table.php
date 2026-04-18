<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('coordinator_id')
                  ->nullable()
                  ->after('department_id')
                  ->constrained('teachers')
                  ->nullOnDelete();
            $table->string('ctevt_code', 50)->nullable()->after('code');
            $table->string('affiliation_type', 50)->default('CTEVT')->after('ctevt_code');
            $table->text('eligibility')->nullable()->after('description');
            $table->string('syllabus')->nullable()->after('eligibility'); // PDF path
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coordinator_id');
            $table->dropColumn(['ctevt_code', 'affiliation_type', 'eligibility', 'syllabus']);
        });
    }
};
