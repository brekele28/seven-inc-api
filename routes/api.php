<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\LogoController;
use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\BisnisKamiFullController;
use App\Http\Controllers\Api\WorksController;
use App\Http\Controllers\Api\JobWorksController;
use App\Http\Controllers\Api\SocialLinkController;

Route::post('/admin/register', [AdminAuthController::class, 'register']);
Route::post('/admin/login',    [AdminAuthController::class, 'login']);

// ========== PUBLIC ==========
Route::get('/admin/logo', [LogoController::class, 'show']);
Route::get('/about', [AboutController::class, 'show']);
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{idOrSlug}', [NewsController::class, 'show']);
Route::get('/bisnis-kami-full', [BisnisKamiFullController::class, 'show']);
Route::get('/works/latest', [WorksController::class, 'latest']);
Route::get('/social-links', [SocialLinkController::class, 'publicIndex']);

// job works CRUD (tetap)
Route::get('/job-works', [JobWorksController::class, 'index']);
Route::get('/job-works/{id}', [JobWorksController::class, 'show']);
Route::post('/job-works', [JobWorksController::class, 'store']);
Route::put('/job-works/{id}', [JobWorksController::class, 'update']);
Route::delete('/job-works/{id}', [JobWorksController::class, 'destroy']);

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

    Route::put('/bisnis-kami-full/text', [BisnisKamiFullController::class, 'updateText']);
    Route::post('/bisnis-kami-full/image', [BisnisKamiFullController::class, 'updateImage']);

    Route::post('/works', [WorksController::class, 'store']);
    Route::patch('/works/{work}', [WorksController::class, 'update']);

    // ==== Social Links (admin) ====
    Route::get('/social-links', [SocialLinkController::class, 'adminIndex']);  // load untuk EditLink.jsx
    Route::put('/social-links', [SocialLinkController::class, 'bulkUpsert']);  // simpan untuk EditLink.jsx
});