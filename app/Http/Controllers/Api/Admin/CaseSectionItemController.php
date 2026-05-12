<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseSectionItemRequest;
use App\Http\Requests\UpdateCaseSectionItemRequest;
use App\Http\Resources\CaseSectionItemResource;
use App\Models\CaseSection;
use App\Models\CaseSectionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="PBL Section Items",
 *     description="Manage section items (Admin/Guru only)"
 * )
 */
class CaseSectionItemController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/pbl-sections/{caseSection}/items",
     *     operationId="createSectionItem",
     *     tags={"PBL Section Items"},
     *     summary="Create a section item (Admin/Guru only)",
     *     description="Create a new item in a section",
     *     security={{"bearer_token":{}}},
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
     *             required={"type"},
     *             @OA\Property(property="type", type="string", enum={"heading","text","list","image"}),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="image_url", type="string"),
     *             @OA\Property(property="order", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item created successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can create items"),
     * )
     */
    public function store(StoreCaseSectionItemRequest $request, CaseSection $caseSection): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || !in_array($user->role, [UserRole::ADMIN, UserRole::GURU], true)) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin and Guru can create items',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $data['section_id'] = $caseSection->id;

        $item = CaseSectionItem::create($data);

        return response()->json([
            'message' => 'Section item created successfully',
            'data' => new CaseSectionItemResource($item),
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/pbl-items/{caseSectionItem}",
     *     operationId="updateSectionItem",
     *     tags={"PBL Section Items"},
     *     summary="Update a section item (Admin/Guru only)",
     *     description="Update an existing section item",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="caseSectionItem",
     *         in="path",
     *         required=true,
     *         description="Item ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"heading","text","list","image"}),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="image_url", type="string"),
     *             @OA\Property(property="order", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item updated successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can update items"),
     * )
     */
    public function update(UpdateCaseSectionItemRequest $request, CaseSectionItem $caseSectionItem): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || !in_array($user->role, [UserRole::ADMIN, UserRole::GURU], true)) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin and Guru can update items',
            ], Response::HTTP_FORBIDDEN);
        }

        $caseSectionItem->update($request->validated());

        return response()->json([
            'message' => 'Section item updated successfully',
            'data' => new CaseSectionItemResource($caseSectionItem),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/pbl-items/{caseSectionItem}",
     *     operationId="deleteSectionItem",
     *     tags={"PBL Section Items"},
     *     summary="Delete a section item (Admin/Guru only)",
     *     description="Delete an existing section item",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="caseSectionItem",
     *         in="path",
     *         required=true,
     *         description="Item ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Only Admin and Guru can delete items"),
     * )
     */
    public function destroy(CaseSectionItem $caseSectionItem): JsonResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || !in_array($user->role, [UserRole::ADMIN, UserRole::GURU], true)) {
            return response()->json([
                'message' => 'Unauthorized - Only Admin and Guru can delete items',
            ], Response::HTTP_FORBIDDEN);
        }

        $caseSectionItem->delete();

        return response()->json([
            'message' => 'Section item deleted successfully',
        ]);
    }
}
