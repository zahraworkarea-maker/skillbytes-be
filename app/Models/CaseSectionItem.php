<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseSectionItem extends Model
{
    use HasFactory;

    protected $table = 'case_section_items';

    protected $fillable = [
        'section_id',
        'type',
        'content',
        'image_url',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the section that owns the item
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CaseSection::class, 'section_id');
    }
}
