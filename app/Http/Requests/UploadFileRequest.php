<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
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
            'file' => 'required|file|max:102400', // max 100MB - accepts all file types
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File is required',
            'file.file' => 'Input must be a file',
            'file.max' => 'File size must not exceed 100MB',
        ];
    }
}
