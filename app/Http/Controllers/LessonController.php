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

    /**
     * @OA\Get(
     *     path="/lessons",
     *     summary="Get paginated lessons",
     *     description="Retrieve paginated list of lessons for authenticated user.",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pagesize",
     *         in="query",
     *         description="Page size (default: 15)",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated lessons",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lessons retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="level", type="integer"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )),
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

        $lessons = $this->lessonService->getPaginatedLessons($request->user(), $pageSize);

        $lessons->setCollection(
            collect(LessonResource::collection($lessons->getCollection())->resolve())
        );

        return $this->paginatedResponse($lessons, 'Lessons retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/lessons/all",
     *     summary="Get all lessons without pagination",
     *     description="Retrieve all available lessons for authenticated user without pagination.",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All lessons",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="All lessons retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getAll(Request $request): JsonResponse
    {
        $lessons = $this->lessonService->getAllLessons($request->user());

        return $this->successResponse(
            LessonResource::collection($lessons),
            'All lessons retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/lessons/{slug}",
     *     summary="Get lesson by slug",
     *     description="Retrieve detailed lesson information by slug.",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Lesson slug"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lesson retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Lesson not found")
     * )
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $lesson = $this->lessonService->getLessonBySlug($slug, $request->user());

        return $this->successResponse(
            new LessonResource($lesson),
            'Lesson retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/lessons/by-level/{level}",
     *     summary="Get lessons by level",
     *     description="Retrieve all lessons for a specific level.",
     *     tags={"Lessons"},
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
     *         description="Lessons by level",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lessons by level retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Level not found")
     * )
     */
    public function byLevel(GetLessonsByLevelRequest $request, int $level): JsonResponse
    {
        $lessons = $this->lessonService->getLessonsByLevelNumber($level, $request->user());

        return $this->successResponse(
            LessonResource::collection($lessons),
            'Lessons by level retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/lessons/completed",
     *     summary="Get completed lessons",
     *     description="Retrieve all lessons completed by the authenticated user.",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Completed lessons",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Completed lessons retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getCompleted(Request $request): JsonResponse
    {
        $lessons = $this->lessonService->getCompletedLessons($request->user());

        return $this->successResponse(
            LessonResource::collection($lessons),
            'Completed lessons retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/lessons",
     *     summary="Create new lesson",
     *     description="Create a new lesson. Only accessible to admin and guru roles.",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","slug","level_id"},
     *             @OA\Property(property="title", type="string", description="Lesson title"),
     *             @OA\Property(property="slug", type="string", description="Unique slug"),
     *             @OA\Property(property="description", type="string", description="Lesson description"),
     *             @OA\Property(property="level_id", type="integer", description="Level ID"),
     *             @OA\Property(property="content", type="string", description="Lesson content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lesson created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lesson created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreLessonRequest $request): JsonResponse
    {
        $lesson = $this->lessonService->createLesson($request->validated());

        return $this->createdResponse(
            new LessonResource($lesson),
            'Lesson created successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/lessons/{slug}",
     *     summary="Update lesson",
     *     description="Update an existing lesson. Use POST for form-data/file uploads. Only accessible to admin and guru roles.",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Lesson slug"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", description="Updated title"),
     *             @OA\Property(property="description", type="string", description="Updated description"),
     *             @OA\Property(property="content", type="string", description="Updated content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lesson updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Lesson not found")
     * )
     */
    public function update(UpdateLessonRequest $request, string $slug): JsonResponse
    {
        $lesson = $this->lessonService->updateLessonBySlug($slug, $request->validated(), $request->user());

        return $this->successResponse(
            new LessonResource($lesson),
            'Lesson updated successfully'
        );
    }

    /**
     * @OA\Delete(
     *     path="/lessons/{slug}",
     *     summary="Delete lesson",
     *     description="Delete a lesson permanently. Only accessible to admin and guru roles.",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Lesson slug"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lesson deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Lesson not found")
     * )
     */
    public function destroy(string $slug): JsonResponse
    {
        $this->lessonService->deleteLessonBySlug($slug);

        return $this->successResponse(null, 'Lesson deleted successfully');
    }
}
