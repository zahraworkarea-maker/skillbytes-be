<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return in_array(auth()->user()->role, [UserRole::ADMIN, UserRole::GURU]);
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($userId)],
            'username' => ['nullable', 'string', Rule::unique('users')->ignore($userId), 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
        ];
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
        ];
    }
}
