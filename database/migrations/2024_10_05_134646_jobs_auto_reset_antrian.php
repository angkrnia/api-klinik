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
        Schema::create('jobs_auto_reset_antrian', function (Blueprint $table) {
            $table->id();
            $table->integer('last_queue');
            $table->string('status_queue')->nullable();
            $table->string('status_jobs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs_auto_reset_antrian');
    }
};
