<?php

namespace App\Services;

use App\Enums\PblCaseStatus;
use App\Models\PblCase;
use App\Models\User;
use Carbon\Carbon;

class PblCaseStatusService
{
    /**
     * Get the status of a case for a specific user
     *
     * @param PblCase $case
     * @param User $user
     * @return PblCaseStatus
     */
    public static function getStatus(PblCase $case, User $user): PblCaseStatus
    {
        $now = Carbon::now();
        
        // Check if user has submitted
        $hasSubmission = $case->submissions()
            ->where('user_id', $user->id)
            ->exists();

        if ($hasSubmission) {
            return PblCaseStatus::COMPLETED;
        }

        // Case hasn't started yet
        if ($now < $case->start_date) {
            return PblCaseStatus::NOT_STARTED;
        }

        // Case is past deadline
        if ($now > $case->deadline) {
            return PblCaseStatus::LATE;
        }

        // Case is in progress (between start_date and deadline)
        return PblCaseStatus::IN_PROGRESS;
    }

    /**
     * Get status as string
     *
     * @param PblCase $case
     * @param User $user
     * @return string
     */
    public static function getStatusString(PblCase $case, User $user): string
    {
        return self::getStatus($case, $user)->value;
    }

    /**
     * Check if case can be started
     *
     * @param PblCase $case
     * @return bool
     */
    public static function canStart(PblCase $case): bool
    {
        return Carbon::now() >= $case->start_date;
    }

    /**
     * Check if case deadline has passed
     *
     * @param PblCase $case
     * @return bool
     */
    public static function isDeadlinePassed(PblCase $case): bool
    {
        return Carbon::now() > $case->deadline;
    }
}
