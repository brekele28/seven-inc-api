<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\LogoController;
use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\BisnisKamiFullController;

Route::post('/admin/register', [AdminAuthController::class, 'register']);
Route::post('/admin/login',    [AdminAuthController::class, 'login']);

// ========== PUBLIC ==========
Route::get('/admin/logo', [LogoController::class, 'show']);
Route::get('/about',      [AboutController::class, 'show']);
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{idOrSlug}', [NewsController::class, 'show']);
Route::get('/works', [WorkController::class, 'index']);
Route::get('/works/latest', [WorkController::class, 'latest']);
Route::get('/works/{id}', [WorkController::class, 'show']);
Route::get('/bisnis-kami-full', [BisnisKamiFullController::class, 'show']);

// ========== ADMIN (auth) ==========
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/update-avatar',  [AdminAuthController::class, 'updateAvatar']);
    Route::post('/update-profile', [AdminAuthController::class, 'updateProfile']);
    Route::post('/logout',         [AdminAuthController::class, 'logout']);

    Route::post('/logo',  [LogoController::class, 'store']);
    Route::post('/about', [AboutController::class, 'store']);
    Route::post('/about/paragraph', [AboutController::class, 'updateParagraph']);
    Route::post('/about/core-text', [AboutController::class, 'updateCoreText']);

    Route::post('/news', [NewsController::class, 'store']);
    Route::patch('/news/{idOrSlug}', [NewsController::class, 'update']);
    Route::delete('/news/{idOrSlug}', [NewsController::class, 'destroy']);
    Route::post('/news/{idOrSlug}/publish', [NewsController::class, 'togglePublish']);

    Route::post('/works', [WorkController::class, 'store']);
    Route::match(['patch', 'post'], '/works/{id}', [WorkController::class, 'update']);
    Route::delete('/works/{id}', [WorkController::class, 'destroy']);

    Route::put('/bisnis-kami-full/text', [BisnisKamiFullController::class, 'updateText']);
    Route::post('/bisnis-kami-full/image', [BisnisKamiFullController::class, 'updateImage']);
});