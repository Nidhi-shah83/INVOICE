<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        static::addGlobalScope('user', static function (Builder $builder): void {
            if (! Auth::check()) {
                return;
            }

            $builder->where($builder->qualifyColumn('user_id'), Auth::id());
        });

        static::creating(static function ($model): void {
            if (! Auth::check() || ! empty($model->user_id)) {
                return;
            }

            $model->user_id = Auth::id();
        });
    }
}
