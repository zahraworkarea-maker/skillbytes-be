<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'duration' => ['nullable', 'string', 'max:100'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'pdf_file.file' => 'File materi harus berupa file yang valid.',
            'pdf_file.mimes' => 'File materi harus berformat PDF.',
            'pdf_file.max' => 'Ukuran file materi maksimal 10MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'pdf_file' => 'file materi',
        ];
    }
}
