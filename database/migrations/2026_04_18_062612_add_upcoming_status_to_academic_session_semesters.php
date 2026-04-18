<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

return new class extends Migration
{
    public function up(): void
    {
        // SQLite stores enums as TEXT, so this is safe.
        // For MySQL, we alter the column to include the new value.
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE academic_session_semesters MODIFY COLUMN status ENUM('upcoming','running','delayed','completed') NOT NULL DEFAULT 'upcoming'");
        }
        // SQLite already accepts any string — no DDL change needed.
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE academic_session_semesters MODIFY COLUMN status ENUM('running','delayed','completed') NOT NULL DEFAULT 'running'");
        }
    }
};
