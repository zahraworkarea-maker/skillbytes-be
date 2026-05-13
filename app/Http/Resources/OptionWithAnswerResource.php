<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionWithAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Used for showing results - includes is_correct
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'label' => $this->label,
            'text' => $this->text,
            'is_correct' => $this->is_correct,
        ];
    }
}
