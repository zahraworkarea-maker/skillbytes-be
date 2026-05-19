<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AttemptAnswer;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssessmentResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have assessments
        $assessments = Assessment::all();
        
        if ($assessments->isEmpty()) {
            $this->command->info('Tidak ada assessment. Jalankan AssessmentSeeder terlebih dahulu.');
            return;
        }

        // Ensure we have users
        $users = User::limit(10)->get();
        
        if ($users->isEmpty()) {
            $users = User::factory(10)->create();
            $this->command->info('10 users baru dibuat.');
        }

        $this->command->info('Membuat assessment results...');

        foreach ($users as $user) {
            foreach ($assessments as $assessment) {
                // Create exactly one attempt per status (due to unique constraint)
                // 70% of users: completed, 20% in progress, 10% timeout
                $rand = rand(1, 100);
                
                if ($rand <= 70) {
                    $attempt = AssessmentAttempt::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'assessment_id' => $assessment->id,
                            'status' => 'COMPLETED',
                        ],
                        [
                            'score' => rand(60, 100) + (rand(0, 99) / 100),
                            'started_at' => now()->subDays(rand(1, 30)),
                            'completed_at' => now()->subDays(rand(0, 10)),
                        ]
                    );
                } elseif ($rand <= 90) {
                    $attempt = AssessmentAttempt::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'assessment_id' => $assessment->id,
                            'status' => 'IN_PROGRESS',
                        ],
                        [
                            'score' => null,
                            'started_at' => now()->subHours(rand(1, 12)),
                            'completed_at' => null,
                        ]
                    );
                } else {
                    $attempt = AssessmentAttempt::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'assessment_id' => $assessment->id,
                            'status' => 'TIMEOUT',
                        ],
                        [
                            'score' => rand(0, 60) + (rand(0, 99) / 100),
                            'started_at' => now()->subDays(rand(1, 30)),
                            'completed_at' => now()->subDays(rand(0, 10)),
                        ]
                    );
                }

                // Create answers for this attempt
                $questions = $assessment->questions;
                
                foreach ($questions as $question) {
                    // For in progress attempts, randomly answer only some questions
                    if ($attempt->status->value === 'IN_PROGRESS' && rand(1, 100) > 70) {
                        continue;
                    }

                    // Skip if answer already exists
                    $existingAnswer = AttemptAnswer::where('attempt_id', $attempt->id)
                        ->where('question_id', $question->id)
                        ->exists();
                    
                    if ($existingAnswer) {
                        continue;
                    }

                    // 85% chance to answer correctly for high performers
                    // 50% chance for average
                    // 30% chance for low performers
                    $scoreChance = rand(1, 100);
                    $isCorrect = match(true) {
                        $scoreChance <= 30 => rand(1, 100) <= 30, // low chance
                        $scoreChance <= 65 => rand(1, 100) <= 50, // medium chance
                        default => rand(1, 100) <= 85, // high chance
                    };

                    $options = $question->options;
                    
                    if ($isCorrect) {
                        $selectedOption = $options->where('is_correct', true)->first() ?? $options->first();
                    } else {
                        $selectedOption = $options->where('is_correct', false)->first() ?? $options->first();
                    }

                    AttemptAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'selected_option_id' => $selectedOption->id,
                        'is_correct' => $selectedOption->is_correct,
                    ]);
                }

                // Update score for completed attempts
                if ($attempt->status->value === 'COMPLETED') {
                    $totalAnswers = $attempt->answers()->count();
                    $correctAnswers = $attempt->answers()->where('is_correct', true)->count();
                    
                    if ($totalAnswers > 0) {
                        $score = ($correctAnswers / $totalAnswers) * 100;
                        $attempt->update(['score' => round($score, 2)]);
                    }
                }
            }
        }

        $this->command->info('✓ Assessment results berhasil dibuat!');
        $this->command->info('Statistik:');
        
        $stats = [
            'Total Attempts' => AssessmentAttempt::count(),
            'Completed' => AssessmentAttempt::where('status', 'COMPLETED')->count(),
            'In Progress' => AssessmentAttempt::where('status', 'IN_PROGRESS')->count(),
            'Timeout' => AssessmentAttempt::where('status', 'TIMEOUT')->count(),
            'Total Answers' => AttemptAnswer::count(),
            'Correct Answers' => AttemptAnswer::where('is_correct', true)->count(),
        ];

        foreach ($stats as $label => $value) {
            $this->command->info("  - $label: $value");
        }
    }
}
