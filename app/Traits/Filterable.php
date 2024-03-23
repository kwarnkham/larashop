<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['status'] ?? null, function (Builder $q, $status) {
            $q->whereIn('status', explode(',', $status));
        });

        $query->when($filters['user_id'] ?? null, function (Builder $q, $userId) {
            $q->where('user_id', $userId);
        });

        return $query;
    }
}
