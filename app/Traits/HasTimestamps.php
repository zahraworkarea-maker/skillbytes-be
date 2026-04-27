<?php

namespace App\Traits;

/**
 * Timestamps trait for models
 *
 * Provides automatic created_at and updated_at management
 */
trait HasTimestamps
{
    public function getIncrementing()
    {
        return false;
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically set created_at and updated_at
        static::creating(function ($model) {
            $now = now();
            if (!$model->created_at) {
                $model->created_at = $now;
            }
            $model->updated_at = $now;
        });

        static::updating(function ($model) {
            $model->updated_at = now();
        });
    }
}
