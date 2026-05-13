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

    /**
     * @OA\Get(
     *     path="/levels",
     *     summary="Get paginated levels",
     *     description="Retrieve paginated list of learning levels.",
     *     tags={"Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pagesize",
     *         in="query",
     *         description="Page size (default: 15)",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated levels",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Levels retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/levels/all",
     *     summary="Get all levels with lessons",
     *     description="Retrieve all learning levels with their associated lessons.",
     *     tags={"Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All levels with lessons",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="All levels retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getAll(Request $request): JsonResponse
    {
        $levels = $this->lessonService->getLevelsWithLessons($request->user());

        return $this->successResponse(
            LevelResource::collection($levels),
            'All levels retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/levels/{level}",
     *     summary="Get level by number",
     *     description="Retrieve detailed information of a specific level by level number.",
     *     tags={"Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="level",
     *         in="path",
     *         required=true,
     *         description="Level number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Level retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Level retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Level not found")
     * )
     */
    public function show(Request $request, int $level): JsonResponse
    {
        $levelData = $this->lessonService->getLevelByNumber($level, $request->user());

        return $this->successResponse(
            new LevelResource($levelData),
            'Level retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/levels",
     *     summary="Create new level",
     *     description="Create a new learning level. Only accessible to admin and guru roles.",
     *     tags={"Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","level_number"},
     *             @OA\Property(property="name", type="string", description="Level name"),
     *             @OA\Property(property="level_number", type="integer", description="Level number"),
     *             @OA\Property(property="description", type="string", description="Level description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Level created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Level created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreLevelRequest $request): JsonResponse
    {
        $level = $this->lessonService->createLevel($request->validated());

        return $this->createdResponse(
            new LevelResource($level),
            'Level created successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/levels/{level}",
     *     summary="Update level",
     *     description="Update an existing learning level. Only accessible to admin and guru roles.",
     *     tags={"Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="level",
     *         in="path",
     *         required=true,
     *         description="Level number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Updated name"),
     *             @OA\Property(property="description", type="string", description="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Level updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Level updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Level not found")
     * )
     */
    public function update(UpdateLevelRequest $request, int $level): JsonResponse
    {
        $updated = $this->lessonService->updateLevelByNumber($level, $request->validated());

        return $this->successResponse(
            new LevelResource($updated),
            'Level updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/levels/{level}",
     *     summary="Delete level",
     *     description="Delete a learning level permanently. Only accessible to admin and guru roles.",
     *     tags={"Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="level",
     *         in="path",
     *         required=true,
     *         description="Level number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Level deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Level deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Level not found")
     * )
     */
    public function destroy(int $level): JsonResponse
    {
        $this->lessonService->deleteLevelByNumber($level);

        return $this->successResponse(null, 'Level deleted successfully');
    }
}
