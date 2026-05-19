<?php

namespace Database\Factories;

use App\Enums\AssessmentAttemptStatus;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssessmentAttempt>
 */
class AssessmentAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-30 days', 'now');
        $status = $this->faker->randomElement(AssessmentAttemptStatus::cases());
        
        $completedAt = null;
        if ($status === AssessmentAttemptStatus::COMPLETED) {
            $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');
        } elseif ($status === AssessmentAttemptStatus::TIMEOUT) {
            $completedAt = clone $startedAt;
            $completedAt->modify('+15 minutes');
        }

        return [
            'user_id' => User::factory(),
            'assessment_id' => Assessment::factory(),
            'score' => $status === AssessmentAttemptStatus::COMPLETED ? $this->faker->randomFloat(2, 0, 100) : null,
            'status' => $status,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ];
    }

    /**
     * State untuk completed attempt
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-30 days', 'now');
            return [
                'status' => AssessmentAttemptStatus::COMPLETED,
                'score' => $this->faker->randomFloat(2, 60, 100),
                'started_at' => $startedAt,
                'completed_at' => $this->faker->dateTimeBetween($startedAt, 'now'),
            ];
        });
    }

    /**
     * State untuk in progress attempt
     */
    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => AssessmentAttemptStatus::IN_PROGRESS,
                'score' => null,
                'completed_at' => null,
            ];
        });
    }

    /**
     * State untuk timeout attempt
     */
    public function timeout(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-30 days', 'now');
            return [
                'status' => AssessmentAttemptStatus::TIMEOUT,
                'score' => $this->faker->randomFloat(2, 0, 60),
                'started_at' => $startedAt,
                'completed_at' => (clone $startedAt)->modify('+15 minutes'),
            ];
        });
    }

    /**
     * State untuk high score attempt
     */
    public function highScore(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-30 days', 'now');
            return [
                'status' => AssessmentAttemptStatus::COMPLETED,
                'score' => $this->faker->randomFloat(2, 85, 100),
                'started_at' => $startedAt,
                'completed_at' => $this->faker->dateTimeBetween($startedAt, 'now'),
            ];
        });
    }

    /**
     * State untuk low score attempt
     */
    public function lowScore(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = $this->faker->dateTimeBetween('-30 days', 'now');
            return [
                'status' => AssessmentAttemptStatus::COMPLETED,
                'score' => $this->faker->randomFloat(2, 0, 50),
                'started_at' => $startedAt,
                'completed_at' => $this->faker->dateTimeBetween($startedAt, 'now'),
            ];
        });
    }
}
