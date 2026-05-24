<?php

namespace App\Policies;

use App\Models\CaseSubmission;
use App\Models\User;

class CaseSubmissionPolicy
{
    /**
     * Determine whether the user can grade submissions.
     */
    public function grade(User $user): bool
    {
        return $user->isAdminOrGuru();
    }

    /**
     * Determine whether the user can view their own submission.
     */
    public function view(User $user, CaseSubmission $submission): bool
    {
        return $user->id === $submission->user_id;
    }
}
