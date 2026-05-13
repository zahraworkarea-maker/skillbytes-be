<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'question_id' => (string) $this->question_id,
            'question_text' => $this->question->text,
            'selected_option' => new OptionWithAnswerResource($this->selectedOption),
            'is_correct' => $this->is_correct,
            'explanation' => $this->question->explanation,
        ];
    }
}
