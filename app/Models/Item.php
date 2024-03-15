<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ItemStatus::class,
        ];
    }
}
