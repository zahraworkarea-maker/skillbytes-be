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
            'user_id' => $this->user_id,
            'answer' => $this->answer,
            'submission_file' => $this->submission_file ? asset('storage/' . $this->submission_file) : null,
            'submission_file_path' => $this->submission_file,
            'submitted_at' => $this->submitted_at,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
