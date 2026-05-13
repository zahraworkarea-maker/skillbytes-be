<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Models\AttemptAnswer;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

class AnswerService
{
    /**
     * Submit answer for a question
     *
     * @throws \Exception
     */
    public function submitAnswer(
        AssessmentAttempt $attempt,
        int $questionId,
        int $selectedOptionId
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

        // Create answer record
        return AttemptAnswer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $questionId,
            'selected_option_id' => $selectedOptionId,
            'is_correct' => $option->is_correct,
        ]);
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
