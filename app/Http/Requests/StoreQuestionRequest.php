<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'questions' => 'required|array|min:1|max:1000',
            'questions.*.text' => 'required|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'questions.required' => 'Questions array is required',
            'questions.array' => 'Questions must be an array',
            'questions.min' => 'At least 1 question is required',
            'questions.max' => 'Maximum 1000 questions allowed per request',
            'questions.*.text.required' => 'Question text is required for each question',
            'questions.*.image.image' => 'Image must be a valid image file',
            'questions.*.image.mimes' => 'Image must be in jpeg, png, jpg, or gif format',
            'questions.*.image.max' => 'Image size must not exceed 5MB',
        ];
    }
}
