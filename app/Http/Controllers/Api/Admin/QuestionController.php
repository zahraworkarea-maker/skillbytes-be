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
     *     path="/assessments/{assessmentId}/questions",
     *     summary="Create multiple questions in bulk for assessment",
     *     description="Add multiple questions to an assessment in a single request. This bulk endpoint allows creating up to 1000 questions at once and associates them with the specified assessment for admin and guru roles.",
     *     tags={"Questions"},
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
     *             required={"questions"},
     *             @OA\Property(
     *                 property="questions",
     *                 type="array",
     *                 description="Array of questions to create",
     *                 @OA\Items(
     *                     @OA\Property(property="text", type="string", example="What is the capital of France?", description="Question text"),
     *                     @OA\Property(property="explanation", type="string", nullable=true, example="France is located in Western Europe.", description="Question explanation")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Questions created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="10 questions created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_created", type="integer", example=10),
     *                 @OA\Property(property="questions", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="assessment_id", type="integer"),
     *                         @OA\Property(property="text", type="string"),
     *                         @OA\Property(property="explanation", type="string", nullable=true)
     *                     )
     *                 )
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
            $validated = $request->validated();
            $questions = $this->questionService->createBulkQuestions($assessment, $validated['questions']);

            $questionsData = array_map(function ($question) {
                return [
                    'id' => $question->id,
                    'assessment_id' => $question->assessment_id,
                    'text' => $question->text,
                    'explanation' => $question->explanation,
                ];
            }, $questions);

            return response()->json([
                'success' => true,
                'message' => count($questions) . ' questions created successfully',
                'data' => [
                    'total_created' => count($questions),
                    'questions' => $questionsData,
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
     *     path="/questions/{id}",
     *     summary="Update question",
     *     description="Update an existing question including text and explanation. This endpoint modifies question details for admin and guru roles.",
     *     tags={"Questions"},
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
     *     path="/questions/{id}",
     *     summary="Delete question",
     *     description="Delete a question including all its options and related answers. This endpoint removes the question from the system for admin and guru roles.",
     *     tags={"Questions"},
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
