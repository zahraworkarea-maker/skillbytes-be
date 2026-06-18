<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Services\KnowledgeTracingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeTracingController extends Controller
{
    private KnowledgeTracingService $ktService;

    public function __construct(KnowledgeTracingService $ktService)
    {
        $this->ktService = $ktService;
    }

    /**
     * @OA\Get(
     *     path="/user/kt/mastery",
     *     summary="Get knowledge tracing mastery for the logged-in student",
     *     tags={"User Knowledge Tracing"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Student mastery retrieved successfully"
     *     )
     * )
     */
    public function getMastery(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->ktService->getStudentMastery($userId);

        return response()->json([
            'success' => true,
            'message' => 'Knowledge tracing mastery retrieved successfully',
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user/kt/recommendations",
     *     summary="Get learning recommendations based on knowledge tracing",
     *     tags={"User Knowledge Tracing"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Recommendations retrieved successfully"
     *     )
     * )
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->ktService->getRecommendations($userId);

        return response()->json([
            'success' => true,
            'message' => 'Learning recommendations retrieved successfully',
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user/kt/mastery/history",
     *     summary="Get student mastery history over time",
     *     tags={"User Knowledge Tracing"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mastery history retrieved successfully"
     *     )
     * )
     */
    public function getMasteryHistory(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->ktService->getMasteryHistory($userId);

        return response()->json([
            'success' => true,
            'message' => 'Mastery history retrieved successfully',
            'data' => $data
        ]);
    }
}
