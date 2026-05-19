<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOptionRequest extends FormRequest
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
            'options' => 'required|array|min:1|max:1000',
            'options.*.label' => 'required|string|max:10',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'options.required' => 'Options array is required',
            'options.array' => 'Options must be an array',
            'options.min' => 'At least 1 option is required',
            'options.max' => 'Maximum 1000 options allowed per request',
            'options.*.label.required' => 'Option label is required for each option',
            'options.*.text.required' => 'Option text is required for each option',
            'options.*.is_correct.required' => 'is_correct flag is required for each option',
        ];
    }
}
