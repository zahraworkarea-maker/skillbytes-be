<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * For students taking assessment - does NOT include is_correct
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'question' => $this->text,
            'image_path' => $this->image_path,
            'options' => OptionResource::collection($this->options),
        ];
    }
}
