<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Models\Option;
use App\Models\Question;
use App\Services\OptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function __construct(
        private OptionService $optionService,
    ) {}

    /**
     * @OA\Post(
     *     path="/admin/questions/{questionId}/options",
     *     summary="Create option for question",
     *     description="Add a new option/answer choice to a question. One option must be marked as correct answer. Only accessible by admin and guru.",
     *     tags={"Admin - Options"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="questionId",
     *         in="path",
     *         required=true,
     *         description="Question ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question_id","label","text","is_correct"},
     *             @OA\Property(property="question_id", type="integer", example=1, description="Question ID - must match URL parameter"),
     *             @OA\Property(property="label", type="string", example="A", description="Option label (A, B, C, D, etc.)"),
     *             @OA\Property(property="text", type="string", example="Paris", description="Option text/content"),
     *             @OA\Property(property="is_correct", type="boolean", example=true, description="Is this the correct answer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Option created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="question_id", type="integer"),
     *                 @OA\Property(property="label", type="string"),
     *                 @OA\Property(property="text", type="string"),
     *                 @OA\Property(property="is_correct", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error or ID mismatch"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Question not found")
     * )
     */
    public function store(int $questionId, StoreOptionRequest $request): JsonResponse
    {
        $question = Question::find($questionId);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        // Validate question_id in request matches URL parameter
        if ($request->question_id != $questionId) {
            return response()->json([
                'success' => false,
                'message' => 'Question ID mismatch',
            ], 400);
        }

        try {
            $option = $this->optionService->createOption($question, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Option created successfully',
                'data' => [
                    'id' => $option->id,
                    'question_id' => $option->question_id,
                    'label' => $option->label,
                    'text' => $option->text,
                    'is_correct' => $option->is_correct,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Put(
     *     path="/admin/options/{id}",
     *     summary="Update option",
     *     description="Update an existing option. Only accessible by admin and guru.",
     *     tags={"Admin - Options"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Option ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="label", type="string", nullable=true, example="B", description="Updated label"),
     *             @OA\Property(property="text", type="string", nullable=true, example="Berlin", description="Updated text"),
     *             @OA\Property(property="is_correct", type="boolean", nullable=true, example=false, description="Updated correct answer flag")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="label", type="string"),
     *                 @OA\Property(property="text", type="string"),
     *                 @OA\Property(property="is_correct", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Option not found")
     * )
     */
    public function update(int $id, UpdateOptionRequest $request): JsonResponse
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found',
            ], 404);
        }

        try {
            $option = $this->optionService->updateOption($option, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Option updated successfully',
                'data' => [
                    'id' => $option->id,
                    'label' => $option->label,
                    'text' => $option->text,
                    'is_correct' => $option->is_correct,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/options/{id}",
     *     summary="Delete option",
     *     description="Delete an option from a question. Only accessible by admin and guru.",
     *     tags={"Admin - Options"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Option ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Option deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Option deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Delete error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Option not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found',
            ], 404);
        }

        try {
            $this->optionService->deleteOption($option);

            return response()->json([
                'success' => true,
                'message' => 'Option deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
