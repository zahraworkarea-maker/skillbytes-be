<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePblLevelRequest;
use App\Http\Requests\UpdatePblLevelRequest;
use App\Http\Resources\PblLevelResource;
use App\Models\PblLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="PBL Levels",
 *     description="Manage PBL levels (GET for all users, CRUD for Admin only)"
 * )
 */
class PblLevelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pbl-levels",
     *     operationId="listPblLevels",
     *     tags={"PBL Levels"},
     *     summary="Get all PBL levels",
     *     description="Retrieve all PBL levels available in the system. Accessible to all authenticated users.",
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of PBL levels retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(): JsonResponse
    {
        $levels = PblLevel::all();

        return response()->json([
            'message' => 'PBL levels retrieved successfully',
            'data' => PblLevelResource::collection($levels),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/pbl-levels/{pblLevel}",
     *     operationId="getPblLevel",
     *     tags={"PBL Levels"},
     *     summary="Get a specific PBL level",
     *     description="Retrieve details of a specific PBL level by ID. Accessible to all authenticated users.",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="pblLevel",
     *         in="path",
     *         required=true,
     *         description="PBL level ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PBL level retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="PBL level not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(PblLevel $pblLevel): JsonResponse
    {
        return response()->json([
            'message' => 'PBL level retrieved successfully',
            'data' => new PblLevelResource($pblLevel),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/pbl-levels",
     *     operationId="createPblLevel",
     *     tags={"PBL Levels"},
     *     summary="Create a new PBL level (Admin only)",
     *     description="Create a new PBL level. Only accessible to Admin role.",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="PBL level data",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Beginner")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="PBL level created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin can create levels")
     * )
     */
    public function store(StorePblLevelRequest $request): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin can create PBL levels',
            ], Response::HTTP_FORBIDDEN);
        }

        $level = PblLevel::create($request->validated());

        return response()->json([
            'message' => 'PBL level created successfully',
            'data' => new PblLevelResource($level),
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/pbl-levels/{pblLevel}",
     *     operationId="updatePblLevel",
     *     tags={"PBL Levels"},
     *     summary="Update a PBL level (Admin only)",
     *     description="Update an existing PBL level. Only accessible to Admin role.",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="pblLevel",
     *         in="path",
     *         required=true,
     *         description="PBL level ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Advanced")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PBL level updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="PBL level not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin can update levels")
     * )
     */
    public function update(UpdatePblLevelRequest $request, PblLevel $pblLevel): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin can update PBL levels',
            ], Response::HTTP_FORBIDDEN);
        }

        $pblLevel->update($request->validated());

        return response()->json([
            'message' => 'PBL level updated successfully',
            'data' => new PblLevelResource($pblLevel),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/pbl-levels/{pblLevel}",
     *     operationId="deletePblLevel",
     *     tags={"PBL Levels"},
     *     summary="Delete a PBL level (Admin only)",
     *     description="Delete an existing PBL level. Only accessible to Admin role.",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="pblLevel",
     *         in="path",
     *         required=true,
     *         description="PBL level ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PBL level deleted successfully"
     *     ),
     *     @OA\Response(response=404, description="PBL level not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin can delete levels")
     * )
     */
    public function destroy(PblLevel $pblLevel): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || $user->role !== UserRole::ADMIN) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin can delete PBL levels',
            ], Response::HTTP_FORBIDDEN);
        }

        $pblLevel->delete();

        return response()->json([
            'message' => 'PBL level deleted successfully',
        ]);
    }
}
