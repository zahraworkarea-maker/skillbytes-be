<?php

namespace App\Services;

use App\Enums\AssessmentAttemptStatus;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AttemptService
{
    /**
     * Start a new assessment attempt
     *
     * @throws \Exception
     */
    public function startAttempt(User $user, Assessment $assessment): AssessmentAttempt
    {
        // Check if user already has active attempt
        $activeAttempt = AssessmentAttempt::where('user_id', $user->id)
            ->where('assessment_id', $assessment->id)
            ->where('status', AssessmentAttemptStatus::IN_PROGRESS)
            ->first();

        if ($activeAttempt) {
            throw new \Exception('User already has an active attempt for this assessment');
        }

        // Create new attempt
        return AssessmentAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'status' => AssessmentAttemptStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    /**
     * Get user's attempt with validation
     */
    public function getAttempt(int $attemptId, User $user): ?AssessmentAttempt
    {
        return AssessmentAttempt::where('id', $attemptId)
            ->where('user_id', $user->id)
            ->with(['assessment', 'answers.question', 'answers.selectedOption'])
            ->first();
    }

    /**
     * Check if attempt has timed out
     */
    public function hasTimedOut(AssessmentAttempt $attempt): bool
    {
        if (!$attempt->started_at || !$attempt->assessment) {
            return false;
        }

        $endTime = $attempt->started_at->addMinutes($attempt->assessment->time_limit);
        return now()->greaterThan($endTime);
    }

    /**
     * Complete attempt and calculate score
     *
     * @throws \Exception
     */
    public function completeAttempt(AssessmentAttempt $attempt): AssessmentAttempt
    {
        // Check if already completed or timeout
        if (!$attempt->isInProgress()) {
            throw new \Exception('Attempt is not in progress');
        }

        // Check timeout
        if ($this->hasTimedOut($attempt)) {
            $attempt->update([
                'status' => AssessmentAttemptStatus::TIMEOUT,
                'completed_at' => now(),
            ]);
            throw new \Exception('Attempt has timed out');
        }

        // Calculate score
        $correctAnswers = $attempt->getCorrectAnswersCount();
        $totalQuestions = $attempt->assessment->questions()->count();
        $score = ($correctAnswers / $totalQuestions) * 100;

        // Update attempt
        $attempt->update([
            'score' => $score,
            'status' => AssessmentAttemptStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        return $attempt;
    }

    /**
     * Get user's results
     */
    public function getUserResults(User $user): Collection
    {
        return $user->assessmentAttempts()
            ->where('status', AssessmentAttemptStatus::COMPLETED)
            ->with(['assessment'])
            ->latest('completed_at')
            ->get();
    }

    /**
     * Get user's specific result
     */
    public function getUserResult(int $attemptId, User $user): ?AssessmentAttempt
    {
        return $user->assessmentAttempts()
            ->where('id', $attemptId)
            ->with(['assessment', 'answers.question', 'answers.selectedOption'])
            ->first();
    }

    /**
     * Get all attempts (admin only)
     */
    public function getAllAttempts()
    {
        return AssessmentAttempt::with(['user', 'assessment'])
            ->latest()
            ->paginate(20);
    }

    /**
     * Get attempt detail (admin only)
     */
    public function getAttemptDetail(int $attemptId): ?AssessmentAttempt
    {
        return AssessmentAttempt::where('id', $attemptId)
            ->with(['user', 'assessment', 'answers.question', 'answers.selectedOption'])
            ->first();
    }
}
