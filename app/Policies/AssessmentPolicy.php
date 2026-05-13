<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    /**
     * Determine whether the user can view any model.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Assessment $assessment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN || $user->role === UserRole::GURU;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Assessment $assessment): bool
    {
        return $user->role === UserRole::ADMIN || $user->role === UserRole::GURU;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Assessment $assessment): bool
    {
        return $user->role === UserRole::ADMIN || $user->role === UserRole::GURU;
    }

    /**
     * Determine whether the user can view results.
     */
    public function viewResults(User $user): bool
    {
        return $user->role === UserRole::ADMIN || $user->role === UserRole::GURU;
    }
}
