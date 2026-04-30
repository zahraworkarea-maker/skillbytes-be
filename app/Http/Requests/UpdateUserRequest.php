<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // Merge file input with regular input for proper validation
        $files = $this->allFiles();
        if ($files) {
            foreach ($files as $key => $file) {
                $this->merge([$key => $file]);
            }
        }

        \Log::info('[UpdateUserRequest] prepareForValidation', [
            'all_input' => $this->all(),
            'all_files' => $this->allFiles(),
            'merged_files' => true,
        ]);
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        \Log::info('[UpdateUserRequest] Raw input received', [
            'all' => $this->all(),
            'files' => $this->allFiles(),
            'method' => $this->method(),
            'content_type' => $this->header('Content-Type'),
        ]);

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($userId)],
            'username' => ['nullable', 'string', Rule::unique('users')->ignore($userId), 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::warning('[UpdateUserRequest] Validation failed', [
            'errors' => $validator->errors()->all(),
            'has_profile_photo' => $this->hasFile('profile_photo'),
            'all_files' => $this->allFiles(),
        ]);
        parent::failedValidation($validator);
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama harus berupa teks',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'username.unique' => 'Username sudah terdaftar',
            'username.max' => 'Username maksimal 50 karakter',
            'password.min' => 'Password minimal 8 karakter',
            'profile_photo.image' => 'File harus berupa gambar',
            'profile_photo.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'profile_photo.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }
}
