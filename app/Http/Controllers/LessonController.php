<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetLessonsByLevelRequest;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Http\Resources\LessonResource;
use App\Services\LessonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function __construct(
        private LessonService $lessonService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pageSize = (int) $request->get('pagesize', 15);
        $pageSize = $pageSize > 0 ? $pageSize : 15;

        $lessons = $this->lessonService->getPaginatedLessons($request->user(), $pageSize);

        $lessons->setCollection(
            collect(LessonResource::collection($lessons->getCollection())->resolve())
        );

        return $this->paginatedResponse($lessons, 'Lessons retrieved successfully');
    }

    public function getAll(Request $request): JsonResponse
    {
        $lessons = $this->lessonService->getAllLessons($request->user());

        return $this->successResponse(
            LessonResource::collection($lessons),
            'All lessons retrieved successfully'
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $lesson = $this->lessonService->getLessonBySlug($slug, $request->user());

        return $this->successResponse(
            new LessonResource($lesson),
            'Lesson retrieved successfully'
        );
    }

    public function byLevel(GetLessonsByLevelRequest $request, int $level): JsonResponse
    {
        $lessons = $this->lessonService->getLessonsByLevelNumber($level, $request->user());

        return $this->successResponse(
            LessonResource::collection($lessons),
            'Lessons by level retrieved successfully'
        );
    }

    public function getCompleted(Request $request): JsonResponse
    {
        $lessons = $this->lessonService->getCompletedLessons($request->user());

        return $this->successResponse(
            LessonResource::collection($lessons),
            'Completed lessons retrieved successfully'
        );
    }

    public function store(StoreLessonRequest $request): JsonResponse
    {
        $lesson = $this->lessonService->createLesson($request->validated());

        return $this->createdResponse(
            new LessonResource($lesson),
            'Lesson created successfully'
        );
    }

    /**
     * Update lesson
     * POST /api/lessons/{slug} - Use POST for form-data/file upload (RECOMMENDED)
     * PUT /api/lessons/{slug} - Use PUT for JSON body only
     *
     * Note: PUT/PATCH with multipart/form-data won't parse form data due to PHP limitation.
     * Always use POST when sending form-data or files.
     */
    public function update(UpdateLessonRequest $request, string $slug): JsonResponse
    {
        $lesson = $this->lessonService->updateLessonBySlug($slug, $request->validated(), $request->user());

        return $this->successResponse(
            new LessonResource($lesson),
            'Lesson updated successfully'
        );
    }

    public function destroy(string $slug): JsonResponse
    {
        $this->lessonService->deleteLessonBySlug($slug);

        return $this->successResponse(null, 'Lesson deleted successfully');
    }
}
