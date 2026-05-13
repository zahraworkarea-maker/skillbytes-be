<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

class QuestionService
{
    /**
     * Create question for assessment
     */
    public function createQuestion(Assessment $assessment, array $data): Question
    {
        $data['assessment_id'] = $assessment->id;
        return Question::create($data);
    }

    /**
     * Update question
     */
    public function updateQuestion(Question $question, array $data): Question
    {
        $question->update($data);
        return $question;
    }

    /**
     * Delete question
     */
    public function deleteQuestion(Question $question): bool
    {
        return $question->delete();
    }

    /**
     * Get question with options
     */
    public function getQuestionWithOptions(int $questionId): ?Question
    {
        return Question::with('options')->find($questionId);
    }

    /**
     * Get assessment questions
     */
    public function getAssessmentQuestions(Assessment $assessment): Collection
    {
        return $assessment->questions()->with('options')->get();
    }

    /**
     * Validate minimum one correct answer exists
     */
    public function hasCorrectAnswer(Question $question): bool
    {
        return $question->options()->where('is_correct', true)->exists();
    }
}
