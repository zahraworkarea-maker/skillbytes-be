<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Models\AttemptAnswer;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use App\Services\DktEngineService;

class AnswerService
{
    private DktEngineService $dktEngine;

    public function __construct(DktEngineService $dktEngine)
    {
        $this->dktEngine = $dktEngine;
    }
    /**
     * Submit answer for a question
     *
     * @throws \Exception
     */
        public function submitAnswer(
            AssessmentAttempt $attempt,
            int $questionId,
            string $selectedOptionId     
        ): AttemptAnswer {
        // Validate attempt is in progress
        if (!$attempt->isInProgress()) {
            throw new \Exception('Assessment attempt is not in progress');
        }

        // Check if attempt has timed out
        if ($attempt->hasTimedOut()) {
            $attempt->update(['status' => 'TIMEOUT']);
            throw new \Exception('Assessment attempt has timed out');
        }

        // Check if question already answered
        $existingAnswer = AttemptAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $questionId)
            ->first();

        if ($existingAnswer) {
            throw new \Exception('Question already answered');
        }

        // Validate question belongs to assessment
        $question = Question::where('id', $questionId)
            ->where('assessment_id', $attempt->assessment_id)
            ->first();

        if (!$question) {
            throw new \Exception('Question does not belong to this assessment');
        }

        // Validate option belongs to question
        $option = Option::where('id', $selectedOptionId)
            ->where('question_id', $questionId)
            ->first();

        if (!$option) {
            throw new \Exception('Option does not belong to this question');
        }

        // Trigger DKT update
        $this->dktEngine->updateMastery(
            $attempt->user_id,
            $question,
            $option->is_correct,
            $attempt->id
        );

        // Create answer record
        return AttemptAnswer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $questionId,
            'selected_option_id' => $selectedOptionId,
            'is_correct' => $option->is_correct,
        ]);
    }

    /**
     * Submit multiple answers for bulk submission
     *
     * @param AssessmentAttempt $attempt
     * @param array $answers Array of answers with keys: question_id, selected_option_id
     * @return array Array with total, success_count, failed_count, and results array
     * @throws \Exception
     */
    public function submitAnswersBulk(
        AssessmentAttempt $attempt,
        array $answers
    ): array {
        // Validate attempt is in progress
        if (!$attempt->isInProgress()) {
            throw new \Exception('Assessment attempt is not in progress');
        }

        // Check if attempt has timed out
        if ($attempt->hasTimedOut()) {
            $attempt->update(['status' => 'TIMEOUT']);
            throw new \Exception('Assessment attempt has timed out');
        }

        $results = [];
        $failedCount = 0;

        // Extract all question IDs and option IDs
        $questionIds = array_column($answers, 'question_id');
        $optionIds = array_column($answers, 'selected_option_id');

        // Fetch all existing answers for this attempt and these questions
        $existingAnswers = AttemptAnswer::where('attempt_id', $attempt->id)
            ->whereIn('question_id', $questionIds)
            ->pluck('id', 'question_id')
            ->toArray();

        // Fetch all valid questions for this assessment
        $validQuestions = Question::whereIn('id', $questionIds)
            ->where('assessment_id', $attempt->assessment_id)
            ->pluck('id')
            ->toArray();

        // Fetch all valid options
        $validOptions = Option::whereIn('id', $optionIds)
            ->whereIn('question_id', $questionIds)
            ->get(['id', 'question_id', 'is_correct'])
            ->keyBy('id');

        $answersToInsert = [];

        foreach ($answers as $answerData) {
            $questionId = $answerData['question_id'];
            $selectedOptionId = $answerData['selected_option_id'];

            try {
                // Check if already answered
                if (isset($existingAnswers[$questionId])) {
                    $results[] = [
                        'question_id' => $questionId,
                        'success' => false,
                        'message' => 'Question already answered',
                        'answer_id' => null,
                        'is_correct' => null,
                    ];
                    $failedCount++;
                    continue;
                }

                // Check if question is valid for this assessment
                if (!in_array($questionId, $validQuestions)) {
                    $results[] = [
                        'question_id' => $questionId,
                        'success' => false,
                        'message' => 'Question does not belong to this assessment',
                        'answer_id' => null,
                        'is_correct' => null,
                    ];
                    $failedCount++;
                    continue;
                }

                // Check if option is valid for this question
                $option = $validOptions->get($selectedOptionId);
                if (!$option || $option->question_id != $questionId) {
                    $results[] = [
                        'question_id' => $questionId,
                        'success' => false,
                        'message' => 'Option does not belong to this question',
                        'answer_id' => null,
                        'is_correct' => null,
                    ];
                    $failedCount++;
                    continue;
                }

                $answersToInsert[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'selected_option_id' => $selectedOptionId,
                    'is_correct' => $option->is_correct,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'question_id' => $questionId,
                    'success' => false,
                    'message' => $e->getMessage(),
                    'answer_id' => null,
                    'is_correct' => null,
                ];
                $failedCount++;
            }
        }

        // Bulk insert valid answers
        if (count($answersToInsert) > 0) {
            AttemptAnswer::insert($answersToInsert);

            // Fetch questions for DKT trigger
            $insertedQuestionIds = array_column($answersToInsert, 'question_id');
            $questionsForDkt = Question::whereIn('id', $insertedQuestionIds)->get()->keyBy('id');

            // Populate successful results and trigger DKT
            foreach ($answersToInsert as $inserted) {
                if (isset($questionsForDkt[$inserted['question_id']])) {
                    $this->dktEngine->updateMastery(
                        $attempt->user_id,
                        $questionsForDkt[$inserted['question_id']],
                        $inserted['is_correct'],
                        $attempt->id
                    );
                }

                $results[] = [
                    'question_id' => $inserted['question_id'],
                    'success' => true,
                    'message' => 'Answer submitted',
                    'answer_id' => $inserted['id'],
                    'is_correct' => $inserted['is_correct'],
                ];
            }
        }

        return [
            'total' => count($answers),
            'success_count' => count($answers) - $failedCount,
            'failed_count' => $failedCount,
            'results' => $results,
        ];
    }

    /**
     * Get answers for an attempt
     */
    public function getAttemptAnswers(AssessmentAttempt $attempt): Collection
    {
        return $attempt->answers()
            ->with(['question', 'selectedOption'])
            ->get();
    }

    /**
     * Check if question is already answered
     */
    public function isQuestionAnswered(AssessmentAttempt $attempt, int $questionId): bool
    {
        return AttemptAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $questionId)
            ->exists();
    }

    /**
     * Get number of answered questions
     */
    public function getAnsweredCount(AssessmentAttempt $attempt): int
    {
        return $attempt->answers()->count();
    }

    /**
     * Get correct answers count
     */
    public function getCorrectAnswersCount(AssessmentAttempt $attempt): int
    {
        return $attempt->answers()
            ->where('is_correct', true)
            ->count();
    }

    /**
     * Calculate score
     */
    public function calculateScore(AssessmentAttempt $attempt): float
    {
        $correctAnswers = $this->getCorrectAnswersCount($attempt);
        $totalQuestions = $attempt->assessment->questions()->count();

        if ($totalQuestions === 0) {
            return 0;
        }

        return ($correctAnswers / $totalQuestions) * 100;
    }
}
