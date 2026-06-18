<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\KnowledgeTracingService;
use Illuminate\Http\JsonResponse;

class KnowledgeTracingController extends Controller
{
    private KnowledgeTracingService $ktService;

    public function __construct(KnowledgeTracingService $ktService)
    {
        $this->ktService = $ktService;
    }

    /**
     * @OA\Get(
     *     path="/admin/kt/class-mastery",
     *     summary="Get class average mastery and students needing attention",
     *     tags={"Admin Knowledge Tracing"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Class mastery retrieved successfully"
     *     )
     * )
     */
    public function getClassMastery(): JsonResponse
    {
        $data = $this->ktService->getClassMastery();

        return response()->json([
            'success' => true,
            'message' => 'Class knowledge tracing mastery retrieved successfully',
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *     path="/admin/kt/students-at-risk",
     *     summary="Get students who are at risk of failing based on knowledge tracing",
     *     tags={"Admin Knowledge Tracing"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="level_id",
     *         in="query",
     *         description="Filter by specific level ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="At risk students retrieved successfully"
     *     )
     * )
     */
    public function getStudentsAtRisk(\Illuminate\Http\Request $request): JsonResponse
    {
        $levelId = $request->query('level_id') ? (int) $request->query('level_id') : null;
        $data = $this->ktService->getStudentsAtRisk($levelId);

        return response()->json([
            'success' => true,
            'message' => 'At risk students retrieved successfully',
            'data' => $data
        ]);
    }
}
