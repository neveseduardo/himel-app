<?php

namespace App\Domain\Shared;

use Illuminate\Support\Str;

trait HasUids
{
    public static function bootHasUids(): void
    {
        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = Str::uuid()->toString();
            }
        });
    }

    protected static function booted(): void
    {
        static::bootHasUids();
    }
}
