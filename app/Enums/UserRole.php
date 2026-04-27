<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case GURU = 'guru';
    case SISWA = 'siswa';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::GURU => 'Guru',
            self::SISWA => 'Siswa',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
