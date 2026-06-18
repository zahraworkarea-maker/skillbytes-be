<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DktTrajectory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skill_id',
        'question_id',
        'attempt_id',
        'is_correct',
        'previous_mastery',
        'new_mastery',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'previous_mastery' => 'decimal:4',
        'new_mastery' => 'decimal:4',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function question(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function attempt(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class);
    }
}
