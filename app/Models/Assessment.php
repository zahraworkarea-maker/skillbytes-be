<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'description',
        'time_limit',
        'level',
        'assessment_level_id',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    public function assessmentLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AssessmentLevel::class, 'assessment_level_id');
    }

    // Relationships
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('created_at', 'asc');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    // Accessors
    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }
}
