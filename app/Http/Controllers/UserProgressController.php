<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarkLessonCompletedRequest;
use App\Http\Resources\LevelResource;
use App\Services\UserProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProgressController extends Controller
{
    public function __construct(
        private UserProgressService $userProgressService
    ) {}

    public function markCompleted(MarkLessonCompletedRequest $request, int $lesson): JsonResponse
    {
        $progress = $this->userProgressService->markLessonAsCompleted($request->user(), $lesson);

        return response()->json([
            'lessonId' => $progress->lesson_id,
            'completed' => (bool) $progress->completed,
            'completedAt' => $progress->completed_at,
        ]);
    }

    public function myProgress(Request $request): JsonResponse
    {
        $progress = $this->userProgressService->getUserProgress($request->user());

        return response()->json([
            'userId' => $progress['userId'],
            'totalLessons' => $progress['totalLessons'],
            'completedLessons' => $progress['completedLessons'],
            'progressPercentage' => $progress['progressPercentage'],
            'levels' => LevelResource::collection($progress['levels'])->resolve(),
        ]);
    }
}
