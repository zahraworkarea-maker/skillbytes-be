<?php

namespace App\Http\Requests;

use App\Rules\MinimumWordCount;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonResumeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userRole = auth()->user()->role->value ?? auth()->user()->role;
        return auth()->check() && in_array($userRole, ['admin', 'guru']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'resume' => ['required', 'string', new MinimumWordCount(300)],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'resume.required' => 'Resume text is required',
            'resume.string' => 'Resume must be text',
        ];
    }
}
