<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLevelRequest;
use App\Http\Requests\UpdateLevelRequest;
use App\Http\Resources\LevelResource;
use App\Services\LessonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function __construct(
        private LessonService $lessonService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pageSize = (int) $request->get('pagesize', 15);
        $pageSize = $pageSize > 0 ? $pageSize : 15;
        $levels = $this->lessonService->getPaginatedLevels($request->user(), $pageSize);

        $levels->setCollection(
            collect(LevelResource::collection($levels->getCollection())->resolve())
        );

        return $this->paginatedResponse($levels, 'Levels retrieved successfully');
    }

    public function getAll(Request $request): JsonResponse
    {
        $levels = $this->lessonService->getLevelsWithLessons($request->user());

        return $this->successResponse(
            LevelResource::collection($levels),
            'All levels retrieved successfully'
        );
    }

    public function show(Request $request, int $level): JsonResponse
    {
        $levelData = $this->lessonService->getLevelByNumber($level, $request->user());

        return $this->successResponse(
            new LevelResource($levelData),
            'Level retrieved successfully'
        );
    }

    public function store(StoreLevelRequest $request): JsonResponse
    {
        $level = $this->lessonService->createLevel($request->validated());

        return $this->createdResponse(
            new LevelResource($level),
            'Level created successfully'
        );
    }

    public function update(UpdateLevelRequest $request, int $level): JsonResponse
    {
        $updated = $this->lessonService->updateLevelByNumber($level, $request->validated());

        return $this->successResponse(
            new LevelResource($updated),
            'Level updated successfully'
        );
    }

    public function destroy(int $level): JsonResponse
    {
        $this->lessonService->deleteLevelByNumber($level);

        return $this->successResponse(null, 'Level deleted successfully');
    }
}
