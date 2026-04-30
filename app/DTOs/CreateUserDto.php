<?php

namespace App\DTOs;

class CreateUserDto extends BaseDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $username,
        public string $password,
        public ?string $role = 'siswa',
        public ?string $profile_photo_path = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            username: $data['username'],
            password: $data['password'],
            role: $data['role'] ?? 'siswa',
            profile_photo_path: $data['profile_photo_path'] ?? null,
        );
    }
}
