<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssessmentResource;
use App\Models\AssessmentLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentLevelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-levels",
     *     summary="List assessment levels with assessments",
     *     description="Retrieve all assessment levels with all assessments that belong to each level, ordered by level number",
     *     tags={"Assessment Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved assessment levels with assessments",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="level_number", type="integer", example=1, description="Numeric level identifier"),
     *                     @OA\Property(property="description", type="string", nullable=true, description="Description of assessment level"),
     *                     @OA\Property(
     *                         property="assessments",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="slug", type="string"),
     *                             @OA\Property(property="title", type="string"),
     *                             @OA\Property(property="description", type="string", nullable=true),
     *                             @OA\Property(property="time_limit", type="integer"),
     *                             @OA\Property(property="total_questions", type="integer"),
     *                             @OA\Property(property="created_at", type="string", format="date-time"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time")
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $levels = AssessmentLevel::with('assessments')->orderBy('level_number')->get();
        
        $data = $levels->map(function ($level) {
            return [
                'id' => $level->id,
                'level_number' => $level->level_number,
                'description' => $level->description,
                'assessments' => AssessmentResource::collection($level->assessments),
                'created_at' => $level->created_at,
                'updated_at' => $level->updated_at,
            ];
        });
        
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * @OA\Get(
     *     path="/assessment-levels/{id}",
     *     summary="Get assessment level with assessments",
     *     description="Retrieve a specific assessment level with all assessments that belong to this level",
     *     tags={"Assessment Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Assessment level ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved assessment level with assessments",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="level_number", type="integer", example=1),
     *                 @OA\Property(property="description", type="string", nullable=true, description="Description of assessment level"),
     *                 @OA\Property(
     *                     property="assessments",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="slug", type="string"),
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="description", type="string", nullable=true),
     *                         @OA\Property(property="time_limit", type="integer"),
     *                         @OA\Property(property="total_questions", type="integer"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assessment level not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $level = AssessmentLevel::with('assessments')->find($id);

        if (!$level) {
            return response()->json(['success' => false, 'message' => 'Assessment level not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $level->id,
                'level_number' => $level->level_number,
                'description' => $level->description,
                'assessments' => AssessmentResource::collection($level->assessments),
                'created_at' => $level->created_at,
                'updated_at' => $level->updated_at,
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/assessment-levels",
     *     summary="Create assessment level",
     *     description="Create a new assessment level with unique level number",
     *     tags={"Assessment Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Assessment level data",
     *         @OA\JsonContent(
     *             required={"level_number"},
     *             @OA\Property(property="level_number", type="integer", example=1, description="Unique numeric level identifier (must be positive)"),
     *             @OA\Property(property="description", type="string", nullable=true, description="Description of assessment level")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Assessment level created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="level_number", type="integer"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed - level_number must be unique and positive"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {

        $data = $request->validate([
            'level_number' => 'required|integer|min:1|unique:assessment_levels,level_number',
            'description' => 'nullable|string',
        ]);

        $level = AssessmentLevel::create($data);

        return response()->json(['success' => true, 'data' => $level], 201);
    }

    /**
     * @OA\Put(
     *     path="/assessment-levels/{id}",
     *     summary="Update assessment level",
     *     description="Update an existing assessment level by ID",
     *     tags={"Assessment Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Assessment level ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Assessment level data to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="level_number", type="integer", description="Numeric level identifier (must be positive and unique)", nullable=true),
     *             @OA\Property(property="description", type="string", nullable=true, description="Description of assessment level")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assessment level updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="level_number", type="integer"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assessment level not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed - level_number must be unique and positive"
     *     )
     * )
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $level = AssessmentLevel::find($id);
        if (! $level) {
            return response()->json(['success' => false, 'message' => 'Level not found'], 404);
        }

        $data = $request->validate([
            'level_number' => 'sometimes|required|integer|min:1|unique:assessment_levels,level_number,' . $id,
            'description' => 'nullable|string',
        ]);

        $level->update($data);

        return response()->json(['success' => true, 'data' => $level]);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-levels/{id}",
     *     summary="Delete assessment level",
     *     description="Delete an assessment level by ID",
     *     tags={"Assessment Levels"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Assessment level ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assessment level deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Level deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Assessment level not found"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $level = AssessmentLevel::find($id);
        if (! $level) {
            return response()->json(['success' => false, 'message' => 'Level not found'], 404);
        }

        $level->delete();

        return response()->json(['success' => true, 'message' => 'Level deleted']);
    }
}
