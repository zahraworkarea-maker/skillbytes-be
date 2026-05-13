<?php

namespace App\Http\Controllers\Api\Admin;

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
     *     path="/admin/results",
     *     summary="Get all assessment results",
     *     description="Retrieve all assessment results from all users with pagination. Only accessible by admin and guru.",
     *     tags={"Admin - Results"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of all assessment results",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="assessment_id", type="integer"),
     *                 @OA\Property(property="score", type="number", format="float"),
     *                 @OA\Property(property="status", type="string", example="COMPLETED"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="count", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(): JsonResponse
    {
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
    }

    /**
     * @OA\Get(
     *     path="/admin/results/{attemptId}",
     *     summary="Get assessment result detail",
     *     description="Retrieve detailed result of a specific assessment attempt including all answers and correctness. Only accessible by admin and guru.",
     *     tags={"Admin - Results"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="attemptId",
     *         in="path",
     *         required=true,
     *         description="Assessment Attempt ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assessment result detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="assessment_id", type="integer"),
     *                 @OA\Property(property="score", type="number", format="float"),
     *                 @OA\Property(property="status", type="string", example="COMPLETED"),
     *                 @OA\Property(property="started_at", type="string", format="date-time"),
     *                 @OA\Property(property="completed_at", type="string", format="date-time"),
     *                 @OA\Property(property="answers", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="question_id", type="integer"),
     *                     @OA\Property(property="question_text", type="string"),
     *                     @OA\Property(property="selected_option_id", type="integer"),
     *                     @OA\Property(property="selected_option_text", type="string"),
     *                     @OA\Property(property="is_correct", type="boolean"),
     *                     @OA\Property(property="correct_option_id", type="integer"),
     *                     @OA\Property(property="correct_option_text", type="string")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Result not found")
     * )
     */
    public function show(int $attemptId): JsonResponse
    {
        $result = $this->attemptService->getAttemptDetail($attemptId);

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
