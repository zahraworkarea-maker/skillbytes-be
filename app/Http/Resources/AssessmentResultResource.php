<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Detailed result with all answers and explanations
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attempt = $this->resource;
        $correctAnswers = $attempt->getCorrectAnswersCount();
        $totalQuestions = $attempt->assessment->questions()->count();

        return [
            'id' => (string) $attempt->id,
            'assessment' => new AssessmentResource($attempt->assessment),
            'score' => $attempt->score,
            'status' => $attempt->status->value,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'started_at' => $attempt->started_at,
            'completed_at' => $attempt->completed_at,
            'answers' => AttemptAnswerResource::collection($attempt->answers()->with(['question', 'selectedOption'])->get()),
        ];
    }
}
