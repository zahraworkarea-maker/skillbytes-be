<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseSectionRequest;
use App\Http\Requests\UpdateCaseSectionRequest;
use App\Http\Resources\CaseSectionResource;
use App\Models\CaseSection;
use App\Models\PblCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="PBL Sections",
 *     description="Manage case sections (Admin/Guru only)"
 * )
 */
class CaseSectionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/pbl-cases/{pblCase}/sections",
     *     operationId="getCaseSections",
     *     tags={"PBL Sections"},
     *     summary="Get sections of a case",
     *     description="Retrieve all sections for a specific case",
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
     *         description="List of sections"
     *     ),
     *     @OA\Response(response=404, description="Case not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function index(PblCase $pblCase): JsonResponse
    {
        $sections = $pblCase->sections()->with('items')->get();
        return response()->json(CaseSectionResource::collection($sections));
    }

    /**
     * @OA\Post(
     *     path="/pbl-cases/{pblCase}/sections",
     *     operationId="createCaseSection",
     *     tags={"PBL Sections"},
     *     summary="Create a new section (Admin/Guru only)",
     *     description="Create a new section for a case",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pblCase",
     *         in="path",
     *         required=true,
     *         description="Case ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Problem Description"),
     *             @OA\Property(property="order", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Section created successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can create sections"),
     * )
     */
    public function store(StoreCaseSectionRequest $request, PblCase $pblCase): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || !in_array($user->role, [UserRole::ADMIN, UserRole::GURU], true)) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin and Guru can create sections',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $data['case_id'] = $pblCase->id;

        $section = CaseSection::create($data);

        return response()->json([
            'message' => 'Section created successfully',
            'data' => new CaseSectionResource($section),
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/pbl-sections/{caseSection}",
     *     operationId="updateCaseSection",
     *     tags={"PBL Sections"},
     *     summary="Update a section (Admin/Guru only)",
     *     description="Update an existing section",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="caseSection",
     *         in="path",
     *         required=true,
     *         description="Section ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="order", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Section updated successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can update sections"),
     * )
     */
    public function update(UpdateCaseSectionRequest $request, CaseSection $caseSection): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || !in_array($user->role, [UserRole::ADMIN, UserRole::GURU], true)) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin and Guru can update sections',
            ], Response::HTTP_FORBIDDEN);
        }

        $caseSection->update($request->validated());

        return response()->json([
            'message' => 'Section updated successfully',
            'data' => new CaseSectionResource($caseSection->load('items')),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/pbl-sections/{caseSection}",
     *     operationId="deleteCaseSection",
     *     tags={"PBL Sections"},
     *     summary="Delete a section (Admin/Guru only)",
     *     description="Delete an existing section",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="caseSection",
     *         in="path",
     *         required=true,
     *         description="Section ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Section deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can delete sections"),
     * )
     */
    public function destroy(CaseSection $caseSection): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || !in_array($user->role, [UserRole::ADMIN, UserRole::GURU], true)) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin and Guru can delete sections',
            ], Response::HTTP_FORBIDDEN);
        }

        $caseSection->delete();

        return response()->json([
            'message' => 'Section deleted successfully',
        ]);
    }
}
