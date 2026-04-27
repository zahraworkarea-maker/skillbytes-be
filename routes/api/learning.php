<?php

use App\Http\Controllers\LessonController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\UserProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/levels', [LevelController::class, 'index']);
Route::get('/lessons', [LessonController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/levels/all', [LevelController::class, 'getAll']);
    Route::get('/levels/{level}', [LevelController::class, 'show']);
    Route::get('/lessons/all', [LessonController::class, 'getAll']);
    Route::get('/lessons/{slug}', [LessonController::class, 'show']);
    Route::get('/lessons/completed', [LessonController::class, 'getCompleted']);
    Route::get('/levels/{level}/lessons', [LessonController::class, 'byLevel']);

    Route::middleware('role:admin,guru')->group(function () {
    Route::post('/levels', [LevelController::class, 'store']);
    Route::put('/levels/{level}', [LevelController::class, 'update']);
    Route::delete('/levels/{level}', [LevelController::class, 'destroy']);

    Route::post('/lessons', [LessonController::class, 'store']);
    Route::put('/lessons/{slug}', [LessonController::class, 'update']);
    Route::delete('/lessons/{slug}', [LessonController::class, 'destroy']);
    });

    Route::post('/lessons/{lesson}/complete', [UserProgressController::class, 'markCompleted']);
    Route::get('/user/progress', [UserProgressController::class, 'myProgress']);
});
