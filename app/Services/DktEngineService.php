<?php

namespace App\Services;

use App\Models\Question;
use App\Models\UserSkillMastery;
use App\Models\DktTrajectory;
use Illuminate\Support\Facades\Log;

class DktEngineService
{
    /**
     * Default prior probability if student has no history for a skill
     */
    private const DEFAULT_PRIOR = 0.10;

    /**
     * Base transit probability (learning rate)
     */
    private const BASE_TRANSIT = 0.15;

    /**
     * Update mastery for all skills associated with a question after a student answers it.
     *
     * @param int $userId
     * @param Question $question
     * @param bool $isCorrect
     * @param int|null $attemptId
     * @return void
     */
    public function updateMastery(int $userId, Question $question, bool $isCorrect, ?int $attemptId = null): void
    {
        // Get all skills related to this question
        $skills = $question->skills;

        if ($skills->isEmpty()) {
            return; // No skills to update
        }

        // Question IRT parameters
        $difficulty = (float) ($question->difficulty_level ?? 0.50);
        $discrimination = (float) ($question->discrimination ?? 1.00);
        $guess = (float) ($question->guess_probability ?? 0.20);

        // Derive slip probability from difficulty (harder questions have higher slip rate)
        // Range: 0.05 to 0.25
        $slip = 0.05 + ($difficulty * 0.20);

        // Derive transit (learning) probability
        // Better discrimination means higher chance of learning from it
        $transit = self::BASE_TRANSIT * $discrimination;

        foreach ($skills as $skill) {
            $this->processSkillUpdate($userId, $skill->id, $question->id, $attemptId, $isCorrect, $guess, $slip, $transit);
        }
    }

    /**
     * Perform the Bayesian Knowledge Tracing (BKT) update for a single skill.
     */
    private function processSkillUpdate(
        int $userId,
        int $skillId,
        int $questionId,
        ?int $attemptId,
        bool $isCorrect,
        float $guess,
        float $slip,
        float $transit
    ): void {
        // Fetch current mastery
        $masteryRecord = UserSkillMastery::firstOrCreate(
            ['user_id' => $userId, 'skill_id' => $skillId],
            ['mastery_probability' => self::DEFAULT_PRIOR]
        );

        $prior = (float) $masteryRecord->mastery_probability;

        // BKT Evidence Update (Calculate posterior given evidence)
        if ($isCorrect) {
            // P(L | Correct)
            $numerator = $prior * (1 - $slip);
            $denominator = $numerator + ((1 - $prior) * $guess);
        } else {
            // P(L | Incorrect)
            $numerator = $prior * $slip;
            $denominator = $numerator + ((1 - $prior) * (1 - $guess));
        }

        $posterior = $denominator > 0 ? ($numerator / $denominator) : 0;

        // BKT Transit Update (Learning transition after the attempt)
        // P(L_new) = P(L | evidence) + (1 - P(L | evidence)) * P(Transit)
        $newMastery = $posterior + ((1 - $posterior) * $transit);

        // Clamp between 0.01 and 0.99 to avoid extreme certainty
        $newMastery = max(0.01, min(0.99, $newMastery));

        // Save new mastery
        $masteryRecord->update([
            'mastery_probability' => $newMastery
        ]);

        // Log trajectory
        DktTrajectory::create([
            'user_id' => $userId,
            'skill_id' => $skillId,
            'question_id' => $questionId,
            'attempt_id' => $attemptId,
            'is_correct' => $isCorrect,
            'previous_mastery' => $prior,
            'new_mastery' => $newMastery,
        ]);

        Log::info("DKT Update for User {$userId}, Skill {$skillId}: Prior={$prior}, Correct={$isCorrect}, New={$newMastery}");
    }
}
