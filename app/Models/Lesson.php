<?php

namespace App\Models;

use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'slug',
        'title',
        'description',
        'duration',
        'pdf_url',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Lesson $lesson): void {
            $lesson->slug = self::generateSlug();
        });
    }

    private static function generateSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(12));
        } while (self::query()->where('slug', $slug)->exists());

        return $slug;
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_lessons')
            ->withPivot(['completed', 'completed_at'])
            ->withTimestamps();
    }
}
