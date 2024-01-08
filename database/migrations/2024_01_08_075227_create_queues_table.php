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
        Schema::create('queue_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('queue');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->string('status')->default('waiting');
            $table->timestamps();

            $table->foreign('patient_id')->on('patients')->references('id')->onDelete('cascade');
            $table->foreign('doctor_id')->on('doctors')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_logs');
    }
};
