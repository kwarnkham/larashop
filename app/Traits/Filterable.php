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

        return $query;
    }
}
