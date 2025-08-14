<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\LogoController;

Route::post('/admin/register', [AdminAuthController::class, 'register']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::get('/admin/logo', [LogoController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/update-avatar', [AdminAuthController::class, 'updateAvatar']);
    Route::post('/admin/update-profile', [AdminAuthController::class, 'updateProfile']);
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
    Route::post('/admin/logo', [LogoController::class, 'store']);
});