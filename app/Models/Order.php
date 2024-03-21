<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class Order extends BaseModel
{
    use BroadcastsEvents, HasFactory;

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        if (App::environment() == 'testing') {
            return [];
        }

        return match ($event) {
            'created' => ['App.Models.Order'],
            default => [$this, $this->user],
        };
    }

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    public function amount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items()->sum(DB::raw('item_order.price*item_order.quantity'))
        );
    }

    public function paidAt(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->payment == null) {
                    return null;
                }

                return $this->payment->status == PaymentStatus::Completed ? $this->payment->updated_at : null;
            }
        );
    }

    public function items()
    {
        return $this->belongsToMany(Item::class)
            ->using(ItemOrder::class)
            ->withTimestamps()
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

    public function saveItems($submittedItems, $data)
    {
        $this->items()->sync(
            $submittedItems->mapWithKeys(
                fn ($item) => [
                    $item->id => [
                        'price' => $item->price,
                        'quantity' => array_column($data['items'], 'quantity', 'id')[$item->id],
                    ],
                ]
            )
        );
    }
}
