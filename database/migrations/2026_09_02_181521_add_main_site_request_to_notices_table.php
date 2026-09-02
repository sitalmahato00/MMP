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
            $table->boolean('main_site_requested')->default(false)->after('popup_to');
            $table->string('main_site_status', 30)->nullable()->after('main_site_requested'); // 'pending', 'approved', 'rejected'
            $table->boolean('request_as_popup')->default(false)->after('main_site_status');
            $table->text('request_note')->nullable()->after('request_as_popup');
            $table->timestamp('main_site_approved_at')->nullable()->after('request_note');
            $table->foreignId('main_site_approved_by')->nullable()->after('main_site_approved_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('main_site_approved_by');
            $table->dropColumn([
                'main_site_requested',
                'main_site_status',
                'request_as_popup',
                'request_note',
                'main_site_approved_at',
            ]);
        });
    }
};
