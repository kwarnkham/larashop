<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    public function amount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->sum('item_order.price')
        );
    }

    public function items()
    {
        return $this->belongsToMany(Item::class)
            ->using(ItemOrder::class)
            ->withPivot(['price', 'quantity']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
