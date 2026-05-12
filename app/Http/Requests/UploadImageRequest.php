<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // max 5MB
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Image file is required',
            'image.image' => 'File must be an image',
            'image.max' => 'Image size must not exceed 5MB',
        ];
    }
}
