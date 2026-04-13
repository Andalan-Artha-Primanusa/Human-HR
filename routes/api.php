<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['api.token', 'verified'])->group(function () {
	Route::get('/me', [AuthController::class, 'me']);
	Route::middleware('role:hr|superadmin')->group(function () {
		Route::get('/users', [UserController::class, 'index']);
		Route::get('/users/{user}', [UserController::class, 'show']);
	});
});

Route::prefix('public')->group(function () {
	Route::get('/users', [UserController::class, 'publicIndex']);
	Route::get('/users/{user}', [UserController::class, 'publicShow']);
});