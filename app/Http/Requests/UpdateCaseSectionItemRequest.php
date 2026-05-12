<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseSectionItemRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty string image_url to null
        if ($this->image_url === '') {
            $this->merge(['image_url' => null]);
        }
    }

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
            'type' => 'sometimes|in:heading,text,list,image',
            'content' => 'nullable|string',
            'image_url' => 'nullable|string',
            'order' => 'sometimes|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Item type must be one of: heading, text, list, image',
        ];
    }
}
