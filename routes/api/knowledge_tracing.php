<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\KnowledgeTracingController as UserKnowledgeTracingController;
use App\Http\Controllers\Api\Admin\KnowledgeTracingController as AdminKnowledgeTracingController;

/*
|--------------------------------------------------------------------------
| Knowledge Tracing Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // User routes (Siswa)
    Route::prefix('user/kt')->group(function () {
        Route::get('mastery', [UserKnowledgeTracingController::class, 'getMastery']);
        Route::get('recommendations', [UserKnowledgeTracingController::class, 'getRecommendations']);
        Route::get('mastery/history', [UserKnowledgeTracingController::class, 'getMasteryHistory']);
    });

    // Admin/Guru routes
    Route::prefix('admin/kt')->middleware('role:admin,guru')->group(function () {
        Route::get('class-mastery', [AdminKnowledgeTracingController::class, 'getClassMastery']);
        Route::get('students-at-risk', [AdminKnowledgeTracingController::class, 'getStudentsAtRisk']);
    });
});
