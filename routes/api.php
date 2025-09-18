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
use App\Http\Controllers\Api\RequirementController;
use App\Http\Controllers\Api\InternshipHeroController;
use App\Http\Controllers\Api\InternshipCoreValueController;
use App\Http\Controllers\Api\InternshipTermsController;
use App\Http\Controllers\Api\InternshipFormationController;
use App\Http\Controllers\Api\InternshipFacilityController;
use App\Http\Controllers\Api\HeroSectionController;

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
Route::get('/internship/hero', [InternshipHeroController::class, 'show']);
Route::get('/internship/core-values', [InternshipCoreValueController::class, 'index']);
Route::get('/internship/terms', [InternshipTermsController::class, 'show']);
Route::get('/internship/formations', [InternshipFormationController::class, 'index']);
Route::get('/internship/facilities', [InternshipFacilityController::class, 'index']);
Route::get('/hero', [HeroSectionController::class, 'show']);
Route::get('/job-works', [JobWorksController::class, 'index']);
Route::get('/job-works/{id}', [JobWorksController::class, 'show']);
Route::post('/job-works', [JobWorksController::class, 'store']);
Route::put('/job-works/{id}', [JobWorksController::class, 'update']);
Route::delete('/job-works/{id}', [JobWorksController::class, 'destroy']);
Route::get('/requirements/by-job/{jobWorkId}', [RequirementController::class, 'showByJob']);
Route::get('/requirements/{id}', [RequirementController::class, 'showPublicById']); // opsional

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
    Route::get('/social-links', [SocialLinkController::class, 'adminIndex']);
    Route::put('/social-links', [SocialLinkController::class, 'bulkUpsert']);
    Route::post('/requirements', [RequirementController::class, 'store']);
    Route::get('/requirements/{id}', [RequirementController::class, 'showAdmin']);
    Route::patch('/requirements/{id}', [RequirementController::class, 'update']);
    Route::delete('/requirements/{id}', [RequirementController::class, 'destroy']);
    Route::post('/requirements/{id}/items', [RequirementController::class, 'storeItem']);
    Route::patch('/requirements/{id}/items/{itemId}', [RequirementController::class, 'updateItem']);
    Route::delete('/requirements/{id}/items/{itemId}', [RequirementController::class, 'destroyItem']);
    Route::put('/requirements/{id}/items/bulk', [RequirementController::class, 'bulkUpsertItems']);
    Route::put('/requirements/{id}/items/reorder', [RequirementController::class, 'reorderItems']);
    Route::put('/internship/hero', [InternshipHeroController::class, 'updateText']);
    Route::post('/internship/hero/image', [InternshipHeroController::class, 'updateImage']);
    Route::put('/internship/core-values/header', [InternshipCoreValueController::class, 'updateHeader']);
    Route::put('/internship/core-values/cards/{card}', [InternshipCoreValueController::class, 'updateCard']);
    Route::post('/internship/core-values/cards/{card}/image', [InternshipCoreValueController::class, 'updateCardImage']);
    Route::put('/internship/core-values/cards/reorder', [InternshipCoreValueController::class, 'reorder']);
    Route::put('/internship/terms/header', [InternshipTermsController::class, 'updateHeader']);
    Route::put('/internship/terms/items/{index}', [InternshipTermsController::class, 'updateItem']);
    Route::put('/internship/formations/header', [InternshipFormationController::class, 'updateHeader']);
    Route::put('/internship/formations/cards/{card}', [InternshipFormationController::class, 'updateCard']);
    Route::post('/internship/formations/cards/{card}/image', [InternshipFormationController::class, 'updateCardImage']);
    Route::put('/internship/facilities/header', [InternshipFacilityController::class, 'updateHeader']);
    Route::put('/internship/facilities/items/{index}', [InternshipFacilityController::class, 'updateItem']);
    Route::post('/hero', [HeroSectionController::class, 'store']);
    Route::get('/hero/{id}', [HeroSectionController::class, 'showAdmin']);
    Route::patch('/hero/{id}', [HeroSectionController::class, 'update']);
    Route::delete('/hero/{id}', [HeroSectionController::class, 'destroy']);
});