<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetLessonsByLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function validationData(): array
    {
        return array_merge($this->all(), [
            'level' => $this->route('level'),
        ]);
    }
}
