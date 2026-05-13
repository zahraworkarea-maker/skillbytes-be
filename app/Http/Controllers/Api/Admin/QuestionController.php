<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Assessment;
use App\Models\Question;
use App\Services\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function __construct(
        private QuestionService $questionService,
    ) {}

    /**
     * @OA\Post(
     *     path="/admin/assessments/{assessmentId}/questions",
     *     summary="Create question for assessment",
     *     description="Add a new question to an assessment. Only accessible by admin and guru.",
     *     tags={"Admin - Questions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="assessmentId",
     *         in="path",
     *         required=true,
     *         description="Assessment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"text"},
     *             @OA\Property(property="text", type="string", example="What is the capital of France?", description="Question text"),
     *             @OA\Property(property="explanation", type="string", nullable=true, example="France is located in Western Europe. Paris is the capital and largest city.", description="Question explanation for review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Question created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Question created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="assessment_id", type="integer"),
     *                 @OA\Property(property="text", type="string"),
     *                 @OA\Property(property="explanation", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Assessment not found")
     * )
     */
    public function store(int $assessmentId, StoreQuestionRequest $request): JsonResponse
    {
        $assessment = Assessment::find($assessmentId);

        if (!$assessment) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment not found',
            ], 404);
        }

        try {
            $question = $this->questionService->createQuestion($assessment, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully',
                'data' => [
                    'id' => $question->id,
                    'assessment_id' => $question->assessment_id,
                    'text' => $question->text,
                    'explanation' => $question->explanation,
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
     *     path="/admin/questions/{id}",
     *     summary="Update question",
     *     description="Update an existing question. Only accessible by admin and guru.",
     *     tags={"Admin - Questions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Question ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="text", type="string", nullable=true, example="What is the capital of Germany?", description="Updated question text"),
     *             @OA\Property(property="explanation", type="string", nullable=true, example="Germany's capital is Berlin.", description="Updated explanation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Question updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Question updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="text", type="string"),
     *                 @OA\Property(property="explanation", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Question not found")
     * )
     */
    public function update(int $id, UpdateQuestionRequest $request): JsonResponse
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        try {
            $question = $this->questionService->updateQuestion($question, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully',
                'data' => [
                    'id' => $question->id,
                    'text' => $question->text,
                    'explanation' => $question->explanation,
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
     *     path="/admin/questions/{id}",
     *     summary="Delete question",
     *     description="Delete a question from an assessment. Only accessible by admin and guru. Deleting a question will also delete all its options and related answers.",
     *     tags={"Admin - Questions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Question ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Question deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Question deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Delete error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Question not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        try {
            $this->questionService->deleteQuestion($question);

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
