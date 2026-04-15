<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('executives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('principal'); // principal or president
            $table->string('designation')->nullable(); // e.g. Executive Director
            $table->string('start_date_bs')->nullable();
            $table->string('end_date_bs')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('avatar')->nullable();
            $table->text('message')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('executives');
    }
};
