<?php

namespace Database\Factories;

use App\Models\AssessmentAttempt;
use App\Models\AttemptAnswer;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttemptAnswer>
 */
class AttemptAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $question = Question::inRandomOrder()->first() ?? Question::factory()->create();
        $options = $question->options;
        
        $selectedOption = $options->random();
        $isCorrect = $selectedOption->is_correct;

        return [
            'attempt_id' => AssessmentAttempt::factory(),
            'question_id' => $question->id,
            'selected_option_id' => $selectedOption->id,
            'is_correct' => $isCorrect,
        ];
    }

    /**
     * State untuk correct answer
     */
    public function correct(): static
    {
        return $this->state(function (array $attributes) {
            $question = Question::inRandomOrder()->first() ?? Question::factory()->create();
            $correctOption = $question->options()->where('is_correct', true)->first();
            
            if (!$correctOption) {
                $correctOption = $question->options->first();
            }

            return [
                'question_id' => $question->id,
                'selected_option_id' => $correctOption->id,
                'is_correct' => true,
            ];
        });
    }

    /**
     * State untuk incorrect answer
     */
    public function incorrect(): static
    {
        return $this->state(function (array $attributes) {
            $question = Question::inRandomOrder()->first() ?? Question::factory()->create();
            $incorrectOption = $question->options()->where('is_correct', false)->first();
            
            if (!$incorrectOption) {
                $incorrectOption = $question->options->first();
            }

            return [
                'question_id' => $question->id,
                'selected_option_id' => $incorrectOption->id,
                'is_correct' => false,
            ];
        });
    }

    /**
     * State untuk specific attempt
     */
    public function forAttempt(AssessmentAttempt $attempt): static
    {
        return $this->state(function (array $attributes) use ($attempt) {
            return [
                'attempt_id' => $attempt->id,
            ];
        });
    }

    /**
     * State untuk specific question
     */
    public function forQuestion(Question $question): static
    {
        return $this->state(function (array $attributes) use ($question) {
            $selectedOption = $question->options->random();
            return [
                'question_id' => $question->id,
                'selected_option_id' => $selectedOption->id,
                'is_correct' => $selectedOption->is_correct,
            ];
        });
    }
}
