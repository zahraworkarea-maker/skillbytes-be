<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
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
        // Support both single answer and bulk answers
        if (is_array($this->input('answers'))) { 
            return [
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|exists:questions,id|integer',
                'answers.*.selected_option_id' => 'required|exists:options,id|string|uuid',
            ];
        }

        return [
            'question_id' => 'required|exists:questions,id|integer',
            'selected_option_id' => 'required|exists:options,id|string|uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'question_id.exists' => 'Question tidak ditemukan',
            'selected_option_id.exists' => 'Option tidak ditemukan',
            'answers.*.question_id.exists' => 'Question tidak ditemukan',
            'answers.*.selected_option_id.exists' => 'Option tidak ditemukan',
            'answers.required' => 'Answers harus diisi',
            'answers.array' => 'Answers harus berupa array',
            'answers.min' => 'Minimal ada 1 jawaban yang dikirim',
        ];
    }
}
