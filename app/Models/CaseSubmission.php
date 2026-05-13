<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseSubmission extends Model
{
    use HasFactory;

    protected $table = 'case_submissions';

    protected $fillable = [
        'user_id',
        'case_id',
        'answer',
        'submission_file',
        'submitted_at',
        'score',
        'feedback',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    /**
     * Get the user who made the submission
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the case for this submission
     */
    public function pblCase(): BelongsTo
    {
        return $this->belongsTo(PblCase::class, 'case_id');
    }
}
