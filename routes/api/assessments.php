<?php

use App\Http\Controllers\Api\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Api\Admin\OptionController;
use App\Http\Controllers\Api\Admin\QuestionController;
use App\Http\Controllers\Api\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Api\User\AssessmentController;
use App\Http\Controllers\Api\User\ResultController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Student endpoints (public for authenticated students)
    Route::prefix('assessments')->controller(AssessmentController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{slug}', 'show');
        Route::post('/{id}/start', 'start');
        Route::post('/{attemptId}/answers', 'submitAnswer');
        Route::post('/{attemptId}/finish', 'finishAttempt');
    });

    // Student results endpoints
    Route::prefix('my-results')->controller(ResultController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{attemptId}', 'show');
    });

    // Admin/Guru endpoints
    Route::middleware('role:admin,guru')->prefix('admin')->group(function () {
        // Assessment CRUD
        Route::controller(AdminAssessmentController::class)->group(function () {
            Route::post('/assessments', 'store');
            Route::put('/assessments/{id}', 'update');
            Route::delete('/assessments/{id}', 'destroy');
        });

        // Question CRUD
        Route::controller(QuestionController::class)->group(function () {
            Route::post('/assessments/{assessmentId}/questions', 'store');
            Route::put('/questions/{id}', 'update');
            Route::delete('/questions/{id}', 'destroy');
        });

        // Option CRUD
        Route::controller(OptionController::class)->group(function () {
            Route::post('/questions/{questionId}/options', 'store');
            Route::put('/options/{id}', 'update');
            Route::delete('/options/{id}', 'destroy');
        });

        // Results Management
        Route::controller(AdminResultController::class)->group(function () {
            Route::get('/results', 'index');
            Route::get('/results/{attemptId}', 'show');
        });
    });
});
