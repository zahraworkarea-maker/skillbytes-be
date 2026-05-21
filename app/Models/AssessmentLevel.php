<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentLevel extends Model
{
    use HasFactory;

    protected $table = 'assessment_levels';

    protected $fillable = [
        'level_number',
        'description',
    ];

    protected $casts = [
        'level_number' => 'integer',
    ];

    /**
     * Get all assessments in this level
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'assessment_level_id');
    }
}
