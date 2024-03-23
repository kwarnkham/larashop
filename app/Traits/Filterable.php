<?php

namespace App\Traits;

use Carbon\Carbon;
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

        $query->when($filters['from'] ?? null, function (Builder $q, $from) {
            $q->where('updated_at', '>=', $from);
        });

        $query->when($filters['to'] ?? null, function (Builder $q, $to) {
            $q->where('updated_at', '<=', (new Carbon($to))->addDay()->subSecond());
        });

        return $query;
    }
}
