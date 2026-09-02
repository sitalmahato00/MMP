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
        Schema::table('notices', function (Blueprint $table) {
            $table->boolean('is_popup')->default(false)->after('is_published');
            $table->string('popup_from_bs', 20)->nullable()->after('is_popup');
            $table->string('popup_to_bs', 20)->nullable()->after('popup_from_bs');
            $table->date('popup_from')->nullable()->after('popup_to_bs');
            $table->date('popup_to')->nullable()->after('popup_from');
            $table->index(['is_popup', 'is_published'], 'idx_notices_popup_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropIndex('idx_notices_popup_published');
            $table->dropColumn(['is_popup', 'popup_from_bs', 'popup_to_bs', 'popup_from', 'popup_to']);
        });
    }
};
