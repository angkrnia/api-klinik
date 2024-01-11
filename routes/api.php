<?php

use Illuminate\Support\Facades\Route;

Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'index']);
Route::put('refresh-token', [App\Http\Controllers\Auth\LoginController::class, 'refreshToken']);
Route::post('register', App\Http\Controllers\Auth\RegisterController::class);
Route::post('forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'resetPassword']);

Route::middleware(['auth:api'])->group(function () {

	Route::apiResource('patients', App\Http\Controllers\PatientController::class)->except(['destroy']);
	Route::get('doctors', [App\Http\Controllers\DoctorController::class, 'index']);
	Route::get('queue', [App\Http\Controllers\QueueController::class, 'index']);
	Route::post('queue', [App\Http\Controllers\QueueController::class, 'store']);

	// ROUTE ADMIN
	Route::middleware(['admin'])->group(function() {
		Route::post('doctors', [App\Http\Controllers\DoctorController::class, 'store']);
		Route::delete('doctors/{doctor}', [App\Http\Controllers\DoctorController::class, 'destroy']);
		Route::delete('patients/{patient}', [App\Http\Controllers\PatientController::class, 'destroy']);
	});

	// ROUTE DOKTER
	Route::middleware(['doctor'])->group(function () {
		Route::put('doctors/{doctor}', [App\Http\Controllers\DoctorController::class, 'update']);
	});

	// ROUTE USERS
	Route::apiResource('users', App\Http\Controllers\UserController::class);
});
