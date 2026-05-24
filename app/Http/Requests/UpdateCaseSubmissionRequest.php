<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'score' => 'nullable|numeric|min:0|max:100',
            'feedback' => 'nullable|string|min:5',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'score.numeric' => 'Score must be a number',
            'score.min' => 'Score must be at least 0',
            'score.max' => 'Score cannot exceed 100',
            'feedback.min' => 'Feedback must be at least 5 characters',
        ];
    }
}
