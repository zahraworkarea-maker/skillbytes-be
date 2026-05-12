<?php

namespace App\Enums;

enum PblCaseStatus: string
{
    case NOT_STARTED = 'not-started';
    case IN_PROGRESS = 'in-progress';
    case COMPLETED = 'completed';
    case LATE = 'late';
}
