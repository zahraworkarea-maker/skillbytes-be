<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSkillMastery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skill_id',
        'mastery_probability',
    ];

    protected $casts = [
        'mastery_probability' => 'decimal:4',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
