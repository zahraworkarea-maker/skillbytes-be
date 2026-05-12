<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCaseProgress extends Model
{
    use HasFactory;

    protected $table = 'user_case_progress';

    protected $fillable = [
        'user_id',
        'case_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who has this progress
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the case for this progress
     */
    public function pblCase(): BelongsTo
    {
        return $this->belongsTo(PblCase::class, 'case_id');
    }
}
