<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function questions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Question::class)->withTimestamps();
    }

    public function masteries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserSkillMastery::class);
    }
}
