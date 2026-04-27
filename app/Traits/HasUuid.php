<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * UUID trait for models
 *
 * Automatically generate UUID for primary key
 */
trait HasUuid
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
