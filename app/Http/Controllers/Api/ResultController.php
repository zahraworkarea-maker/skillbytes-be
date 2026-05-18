<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssessmentResultResource;
use App\Http\Resources\AssessmentAttemptResource;
use App\Services\AttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(
        private AttemptService $attemptService,
    ) {}

    /**
     * @OA\Get(
     *     path="/results",
     *     summary="Get assessment results",
     *     description="Retrieve assessment results. Users see only their own results, admins/gurus see all results with pagination.",
     *     tags={"Results"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination (admin only)",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page (admin only)",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of assessment results",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="user_id", type="string"),
     *                 @OA\Property(property="assessment", type="object"),
     *                 @OA\Property(property="score", type="number"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="completed_at", type="string", format="date-time"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin', 'guru');

        if ($isAdmin) {
            // Admin sees all results with pagination
            $results = $this->attemptService->getAllAttempts();
            return response()->json([
                'success' => true,
                'data' => AssessmentAttemptResource::collection($results->items()),
                'pagination' => [
                    'total' => $results->total(),
                    'count' => $results->count(),
                    'per_page' => $results->perPage(),
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                ],
            ]);
        } else {
            // User sees only their results
            $results = $this->attemptService->getUserResults($user);
            return response()->json([
                'success' => true,
                'data' => AssessmentAttemptResource::collection($results),
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/results/{attemptId}",
     *     summary="Get specific result detail",
     *     description="Retrieve detailed result of a specific assessment attempt. Users can only view their own results, admins/gurus can view any result.",
     *     tags={"Results"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="attemptId",
     *         in="path",
     *         required=true,
     *         description="Assessment Attempt ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assessment result detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Result not found")
     * )
     */
    public function show(int $attemptId, Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin', 'guru');

        if ($isAdmin) {
            // Admin can view any result
            $result = $this->attemptService->getAttemptDetail($attemptId);
        } else {
            // User can only view their own result
            $result = $this->attemptService->getUserResult($attemptId, $user);
        }

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Result not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AssessmentResultResource($result),
        ]);
    }
}
