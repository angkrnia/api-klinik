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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('phone');
            $table->string('avatar')->nullable();
            $table->string('gender', 1)->nullable();
            $table->text('description')->nullable();
            $table->string('start_day', 10)->nullable();
            $table->string('end_day', 10)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
