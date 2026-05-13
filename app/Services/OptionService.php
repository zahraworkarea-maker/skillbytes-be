<?php

namespace App\Services;

use App\Models\Option;
use App\Models\Question;

class OptionService
{
    /**
     * Create option for question
     */
    public function createOption(Question $question, array $data): Option
    {
        $data['question_id'] = $question->id;
        return Option::create($data);
    }

    /**
     * Update option
     */
    public function updateOption(Option $option, array $data): Option
    {
        $option->update($data);
        return $option;
    }

    /**
     * Delete option
     */
    public function deleteOption(Option $option): bool
    {
        return $option->delete();
    }

    /**
     * Get option with question
     */
    public function getOptionWithQuestion(int $optionId): ?Option
    {
        return Option::with('question')->find($optionId);
    }

    /**
     * Validate option belongs to question
     */
    public function belongsToQuestion(Option $option, Question $question): bool
    {
        return $option->question_id === $question->id;
    }

    /**
     * Check if question has correct answer
     */
    public function questionHasCorrectAnswer(Question $question): bool
    {
        return $question->options()->where('is_correct', true)->exists();
    }
}
