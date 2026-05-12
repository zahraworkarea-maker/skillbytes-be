<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseSection extends Model
{
    use HasFactory;

    protected $table = 'case_sections';

    protected $fillable = [
        'case_id',
        'title',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the case that owns the section
     */
    public function pblCase(): BelongsTo
    {
        return $this->belongsTo(PblCase::class, 'case_id');
    }

    /**
     * Get all items for this section
     */
    public function items(): HasMany
    {
        return $this->hasMany(CaseSectionItem::class, 'section_id')->orderBy('order');
    }
}
