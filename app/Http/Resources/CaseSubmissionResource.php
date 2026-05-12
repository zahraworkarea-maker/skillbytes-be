<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseSubmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'answer' => $this->answer,
            'submitted_at' => $this->submitted_at,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
