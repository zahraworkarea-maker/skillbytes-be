<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkLessonCompletedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'lesson' => ['required', 'integer', 'exists:lessons,id'],
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'lesson' => $this->route('lesson'),
        ]);
    }
}
