<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * For students taking assessment - does NOT include is_correct in options
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'total_questions' => $this->questions()->count(),
            'time_limit' => $this->time_limit,
            'questions' => QuestionResource::collection($this->questions),
        ];
    }
}
