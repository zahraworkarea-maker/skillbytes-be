<?php

namespace App\Models;

use App\Enums\AssessmentAttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assessment_id',
        'score',
        'status',
        'level',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'status' => AssessmentAttemptStatus::class,
        'level' => 'string',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class, 'attempt_id');
    }

    // Methods
    public function isCompleted(): bool
    {
        return $this->status === AssessmentAttemptStatus::COMPLETED;
    }

    public function isInProgress(): bool
    {
        return $this->status === AssessmentAttemptStatus::IN_PROGRESS;
    }

    public function isTimeout(): bool
    {
        return $this->status === AssessmentAttemptStatus::TIMEOUT;
    }

    public function hasTimedOut(): bool
    {
        if (!$this->started_at || !$this->assessment) {
            return false;
        }

        $duration = $this->started_at->addMinutes($this->assessment->time_limit);
        return now()->greaterThan($duration);
    }

    public function getCorrectAnswersCount(): int
    {
        return $this->answers()->where('is_correct', true)->count();
    }

    /**
     * Determine level string based on numeric score using fixed thresholds.
     *
     * - 0-40 => Novice
     * - 41-70 => Intermediate
     * - 71-100 => Advanced
     */
    public static function determineLevel(float $score): string
    {
        if ($score >= 71) {
            return 'Advanced';
        }

        if ($score >= 41) {
            return 'Intermediate';
        }

        return 'Novice';
    }
}
