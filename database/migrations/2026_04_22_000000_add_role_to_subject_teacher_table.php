<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_teacher', function (Blueprint $table) {
            if (!Schema::hasColumn('subject_teacher', 'role')) {
                $table->string('role')->nullable()->after('section');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subject_teacher', function (Blueprint $table) {
            if (Schema::hasColumn('subject_teacher', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
