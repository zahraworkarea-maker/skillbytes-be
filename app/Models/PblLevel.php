<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PblLevel extends Model
{
    use HasFactory;

    protected $table = 'pbl_levels';

    protected $fillable = [
        'name',
    ];

    /**
     * Get all PBL cases for this level
     */
    public function pblCases(): HasMany
    {
        return $this->hasMany(PblCase::class, 'level_id');
    }
}
