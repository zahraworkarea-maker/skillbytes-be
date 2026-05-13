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
            'question_id' => 'required|exists:questions,id',
            'label' => 'required|string|max:10',
            'text' => 'required|string',
            'is_correct' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.exists' => 'Question tidak ditemukan',
        ];
    }
}
