<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDetailsColumn = Schema::hasColumn('subjects', 'details');
        $hasSyllabusColumn = Schema::hasColumn('subjects', 'syllabus');

        if ($hasDetailsColumn && $hasSyllabusColumn) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) use ($hasDetailsColumn, $hasSyllabusColumn) {
            if (! $hasDetailsColumn) {
                $table->text('details')->nullable();
            }

            if (! $hasSyllabusColumn) {
                $table->string('syllabus')->nullable();
            }
        });
    }

    public function down(): void
    {
        $dropDetailsColumn = Schema::hasColumn('subjects', 'details');
        $dropSyllabusColumn = Schema::hasColumn('subjects', 'syllabus');

        if (! $dropDetailsColumn && ! $dropSyllabusColumn) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) use ($dropDetailsColumn, $dropSyllabusColumn) {
            $columns = [];

            if ($dropDetailsColumn) {
                $columns[] = 'details';
            }

            if ($dropSyllabusColumn) {
                $columns[] = 'syllabus';
            }

            $table->dropColumn($columns);
        });
    }
};
