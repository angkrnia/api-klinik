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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname', 150);
            $table->string('email', 100)->unique()->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('role', 20)->default('patient');
            $table->string('password');
            $table->text('refresh_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('remember_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
