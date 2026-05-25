<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserResumeRequest;
use App\Http\Requests\UpdateUserResumeRequest;
use App\Http\Resources\UserResumeResource;
use App\Models\UserResume;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserResumeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/user/resumes",
     *     summary="Get all user resumes",
     *     description="Retrieve all resumes for the authenticated user. Can filter by lesson_id.",
     *     tags={"User Resumes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lesson_id",
     *         in="query",
     *         description="Filter resumes by lesson ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User resumes retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resumes retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="lesson_id", type="integer"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = UserResume::where('user_id', $request->user()->id);

        // Filter by lesson_id if provided
        if ($request->has('lesson_id')) {
            $query->where('lesson_id', $request->input('lesson_id'));
        }

        $resumes = $query->orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            UserResumeResource::collection($resumes),
            'Resumes retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/user/resumes",
     *     summary="Create new resume for a lesson",
     *     description="Create a new resume for the authenticated user for a specific lesson.",
     *     tags={"User Resumes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"lesson_id", "content"},
     *             @OA\Property(property="lesson_id", type="integer", description="Lesson ID this resume is for", example=5),
     *             @OA\Property(property="content", type="string", description="Resume content", example="This lesson covered data structures including arrays, linked lists..."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resume created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resume created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="lesson_id", type="integer"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(StoreUserResumeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = $request->user()->id;
            
            $resume = UserResume::create($validated);

            return $this->createdResponse(
                new UserResumeResource($resume),
                'Resume created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create resume: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/user/resumes/{id}",
     *     summary="Get single resume",
     *     description="Retrieve a specific resume by ID for the authenticated user.",
     *     tags={"User Resumes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Resume ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resume retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resume retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - Resume belongs to another user"),
     *     @OA\Response(response=404, description="Resume not found")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $resume = UserResume::find($id);

        if (!$resume) {
            return $this->notFoundResponse('Resume not found');
        }

        // Check if resume belongs to authenticated user
        if ($resume->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized access to this resume', 403);
        }

        return $this->successResponse(
            new UserResumeResource($resume),
            'Resume retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/user/resumes/{id}",
     *     summary="Update resume",
     *     description="Update the content of an existing resume for the authenticated user.",
     *     tags={"User Resumes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Resume ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", description="Resume content"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resume updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resume updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Resume not found")
     * )
     */
    public function update(UpdateUserResumeRequest $request, int $id): JsonResponse
    {
        try {
            $resume = UserResume::find($id);

            if (!$resume) {
                return $this->notFoundResponse('Resume not found');
            }

            // Check if resume belongs to authenticated user
            if ($resume->user_id !== $request->user()->id) {
                return $this->errorResponse('Unauthorized access to this resume', 403);
            }

            $validated = $request->validated();
            $resume->update($validated);

            return $this->successResponse(
                new UserResumeResource($resume),
                'Resume updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update resume: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/user/resumes/{id}",
     *     summary="Delete resume",
     *     description="Delete a resume for the authenticated user.",
     *     tags={"User Resumes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Resume ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resume deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resume deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Resume not found")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $resume = UserResume::find($id);

            if (!$resume) {
                return $this->notFoundResponse('Resume not found');
            }

            // Check if resume belongs to authenticated user
            if ($resume->user_id !== $request->user()->id) {
                return $this->errorResponse('Unauthorized access to this resume', 403);
            }

            $resume->delete();

            return $this->successResponse(null, 'Resume deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete resume: ' . $e->getMessage(), 500);
        }
    }
}
