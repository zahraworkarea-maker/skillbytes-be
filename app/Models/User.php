<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'is_active',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => UserRole::class,
        'is_active' => 'boolean',
    ];

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'user_lessons')
            ->withPivot(['completed', 'completed_at'])
            ->withTimestamps();
    }

    public function completedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'user_lessons')
            ->wherePivot('completed', true)
            ->withPivot(['completed', 'completed_at'])
            ->withTimestamps();
    }

    public function caseProgress(): HasMany
    {
        return $this->hasMany(UserCaseProgress::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CaseSubmission::class);
    }
}
