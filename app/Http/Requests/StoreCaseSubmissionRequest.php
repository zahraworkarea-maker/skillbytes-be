<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseSubmissionRequest extends FormRequest
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
            'case_id' => 'required|integer|exists:pbl_cases,id',
            'answer' => 'nullable|string|min:10',
            'submission_file' => 'nullable|file',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'answer.min' => 'Answer must be at least 10 characters',
            'submission_file.file' => 'The submission file must be a valid file',
        ];
    }
}
