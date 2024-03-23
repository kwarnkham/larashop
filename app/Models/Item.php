<?php

namespace App\Models;

use App\Enums\ItemStatus;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends BaseModel
{
    use Filterable, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ItemStatus::class,
        ];
    }

    public function pictures()
    {
        return $this->morphMany(Picture::class, 'pictureable');
    }
}
