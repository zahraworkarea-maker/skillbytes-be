<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserResumeRequest extends FormRequest
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
            'lesson_id' => 'required|integer|exists:lessons,id',
            'content' => 'required|string|min:10',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'lesson_id.required' => 'Lesson ID is required',
            'lesson_id.exists' => 'Selected lesson does not exist',
            'content.required' => 'Resume content is required',
            'content.min' => 'Resume content must be at least 10 characters',
        ];
    }
}
