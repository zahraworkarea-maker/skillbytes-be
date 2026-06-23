<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => (string) $this->id,
            'label' => $this->label,
            'text' => $this->text,
        ];

        // Include is_correct only for admin and guru
        if ($request->user() && $request->user()->isAdminOrGuru()) {
            $data['is_correct'] = (bool) $this->is_correct;
        }

        return $data;
    }
}
