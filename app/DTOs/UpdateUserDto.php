<?php

namespace App\DTOs;

class UpdateUserDto extends BaseDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $role = null,
        public ?string $profile_photo_path = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            role: $data['role'] ?? null,
            profile_photo_path: $data['profile_photo_path'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'password' => $this->password,
            'role' => $this->role,
            'profile_photo_path' => $this->profile_photo_path,
        ], fn($value) => $value !== null);
    }
}
