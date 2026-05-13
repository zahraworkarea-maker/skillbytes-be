<?php

namespace App\Policies;

use App\Models\AssessmentAttempt;
use App\Models\User;

class AssessmentAttemptPolicy
{
    /**
     * Determine whether the user can view the attempt.
     */
    public function view(User $user, AssessmentAttempt $attempt): bool
    {
        // User can view their own attempt
        if ($attempt->user_id === $user->id) {
            return true;
        }

        // Admin and Guru can view any attempt
        return $user->isAdmin() || $user->isGuru();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }
}
