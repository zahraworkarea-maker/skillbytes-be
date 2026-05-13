<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionWithAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * For showing results - includes explanation and correct answer
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'question' => $this->text,
            'explanation' => $this->explanation,
            'options' => OptionWithAnswerResource::collection($this->options),
        ];
    }
}
