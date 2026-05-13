<?php

namespace App\Enums;

enum AssessmentAttemptStatus: string
{
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case TIMEOUT = 'TIMEOUT';

    public function label(): string
    {
        return match($this) {
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::TIMEOUT => 'Timeout',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
