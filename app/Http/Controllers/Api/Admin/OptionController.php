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
     *     path="/questions/{questionId}/options",
     *     summary="Create multiple options in bulk for question",
     *     description="Add multiple option/answer choices to a question in a single request. This bulk endpoint allows creating up to 1000 options at once for admin and guru roles. At least one option must be marked as the correct answer.",
     *     tags={"Options"},
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
     *             required={"options"},
     *             @OA\Property(
     *                 property="options",
     *                 type="array",
     *                 description="Array of options to create",
     *                 @OA\Items(
     *                     @OA\Property(property="label", type="string", example="A", description="Option label (A, B, C, D, etc.)"),
     *                     @OA\Property(property="text", type="string", example="Paris", description="Option text/content"),
     *                     @OA\Property(property="is_correct", type="boolean", example=true, description="Is this the correct answer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Options created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="4 options created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_created", type="integer", example=4),
     *                 @OA\Property(property="options", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="question_id", type="integer"),
     *                         @OA\Property(property="label", type="string"),
     *                         @OA\Property(property="text", type="string"),
     *                         @OA\Property(property="is_correct", type="boolean")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
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

        try {
            $validated = $request->validated();
            $options = $this->optionService->createBulkOptions($question, $validated['options']);

            $optionsData = array_map(function ($option) {
                return [
                    'id' => $option->id,
                    'question_id' => $option->question_id,
                    'label' => $option->label,
                    'text' => $option->text,
                    'is_correct' => $option->is_correct,
                ];
            }, $options);

            return response()->json([
                'success' => true,
                'message' => count($options) . ' options created successfully',
                'data' => [
                    'total_created' => count($options),
                    'options' => $optionsData,
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
     *     path="/options/{id}",
     *     summary="Update option",
     *     description="Update an existing option including label, text, and correct answer flag. This endpoint modifies option details for admin and guru roles.",
     *     tags={"Options"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Option ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
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
     *                 @OA\Property(property="id", type="string", format="uuid"),
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
    public function update(string $id, UpdateOptionRequest $request): JsonResponse
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
     *     path="/options/{id}",
     *     summary="Delete option",
     *     description="Delete an option from a question. This endpoint removes the answer choice from the system for admin and guru roles.",
     *     tags={"Options"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Option ID (UUID)",
     *         @OA\Schema(type="string", format="uuid")
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
    public function destroy(string $id): JsonResponse
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
