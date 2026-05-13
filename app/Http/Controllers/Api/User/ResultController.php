<?php

namespace App\Http\Controllers\Api\User;

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
     *     path="/my-results",
     *     summary="Get all user's assessment results",
     *     description="Retrieve all completed assessment attempts for the authenticated user",
     *     tags={"Results"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user's results",
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
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $results = $this->attemptService->getUserResults($user);

        return response()->json([
            'success' => true,
            'data' => AssessmentAttemptResource::collection($results),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/my-results/{attemptId}",
     *     summary="Get user's specific result detail",
     *     description="Retrieve detailed result of a specific assessment attempt including all answers, options, and correct answers for review",
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
     *     @OA\Response(response=404, description="Result not found")
     * )
     */
    public function show(int $attemptId, Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->attemptService->getUserResult($attemptId, $user);

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
