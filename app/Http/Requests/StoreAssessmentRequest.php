<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
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
            'slug' => 'required|string|unique:assessments,slug',
            'assessment_level_id' => 'required|integer|exists:assessment_levels,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'Slug assessment sudah digunakan',
            'assessment_level_id.required' => 'Assessment level harus dipilih',
            'assessment_level_id.exists' => 'Assessment level tidak ditemukan',
            'time_limit.min' => 'Waktu minimal harus 1 menit',
        ];
    }
}
