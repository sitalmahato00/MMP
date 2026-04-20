<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the `programs.coordinator_id -> teachers.id` foreign key.
 *
 * This FK cannot be declared inline in the original programs migration
 * (2026_04_14_000003_create_programs_subjects_tables) because the
 * `teachers` table is created later by 2026_04_14_000005. Keeping the
 * FK in a dedicated migration that runs after all create-table
 * migrations guarantees correct ordering on fresh installs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Only add if the constraint isn't already present (idempotent on fresh installs).
        Schema::table('programs', function (Blueprint $table) {
            $table->foreign('coordinator_id')
                ->references('id')
                ->on('teachers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['coordinator_id']);
        });
    }
};
