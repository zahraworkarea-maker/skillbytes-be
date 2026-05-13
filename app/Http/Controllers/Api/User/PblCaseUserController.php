<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\PblCaseDetailResource;
use App\Http\Resources\PblCaseResource;
use App\Models\PblCase;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="PBL Cases",
 *     description="PBL case management (GET for all users, CRUD for Admin/Guru only)"
 * )
 */
class PblCaseUserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/pbl-cases",
     *     operationId="listPblCases",
     *     tags={"PBL Cases"},
     *     summary="List all PBL cases",
     *     description="Retrieve all available PBL cases with current user's status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="pbl_level_id",
     *         in="query",
     *         description="Filter by PBL level ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of PBL cases",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", items=@OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="case_number", type="integer"),
     *                 @OA\Property(property="image_url", type="string"),
     *                 @OA\Property(property="time_limit", type="integer"),
     *                 @OA\Property(property="start_date", type="string", format="date-time"),
     *                 @OA\Property(property="deadline", type="string", format="date-time"),
     *                 @OA\Property(property="status", type="string", enum={"not-started","in-progress","completed","late"}),
     *                 @OA\Property(property="pbl_level", type="object"),
     *             )),
     *             @OA\Property(property="pagination", type="object"),
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        $query = PblCase::with('level')
            ->orderBy('start_date', 'asc');

        // Filter by level if provided
        if (request()->has('level_id')) {
            $query->where('level_id', request()->input('level_id'));
        }

        $cases = $query->paginate(15);

        // Add status to each case
        $cases->getCollection()->transform(function ($case) use ($user) {
            return (new PblCaseResource($case))->setUser($user);
        });

        return response()->json($cases);
    }

    /**
     * @OA\Get(
     *     path="/pbl-cases/{pblCase}",
     *     operationId="getPblCase",
     *     tags={"PBL Cases"},
     *     summary="Get PBL case details",
     *     description="Retrieve full details of a specific PBL case including all sections and items",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pblCase",
     *         in="path",
     *         required=true,
     *         description="Case ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detailed PBL case information",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="case_number", type="integer"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="image_url", type="string"),
     *             @OA\Property(property="time_limit", type="integer"),
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="deadline", type="string", format="date-time"),
     *             @OA\Property(property="status", type="string", enum={"not-started","in-progress","completed","late"}),
     *             @OA\Property(property="sections", type="array", items=@OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="order", type="integer"),
     *                 @OA\Property(property="items", type="array", items=@OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="type", type="string", enum={"heading","text","list","image"}),
     *                     @OA\Property(property="content", type="string"),
     *                     @OA\Property(property="image_url", type="string"),
     *                     @OA\Property(property="order", type="integer"),
     *                 )),
     *             )),
     *         )
     *     ),
     *     @OA\Response(response=404, description="Case not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function show(PblCase $pblCase): JsonResponse
    {
        $pblCase->load('level', 'sections.items');
        return response()->json(new PblCaseDetailResource($pblCase));
    }
}
