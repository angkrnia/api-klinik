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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('queue_id');
            $table->string('blood_pressure')->default('120/80');
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('temperature', 5, 2)->default(36);
            $table->string('complaint')->nullable();
            $table->string('diagnosa')->nullable();
            $table->string('saran')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->on('patients')->references('id')->onDelete('cascade');
            $table->foreign('queue_id')->on('queue_logs')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
