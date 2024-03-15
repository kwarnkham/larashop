<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:items'],
            'items.*.quantity' => ['required', 'numeric'],
        ]);

        $submittedItems = Item::query()->whereIn('id', array_map(
            fn ($value) => $value['id'],
            $data['items']
        ))->get();

        $submittedItems->each(function ($submittedItem) {
            if ($submittedItem->status == 'inactive') {
                abort(
                    HttpStatus::BAD_REQUEST->value,
                    "The item '{$submittedItem->name}' is inactive."
                );
            }
        });

        $user = $request->user();

        $order = Order::query()->create([
            'user_id' => $user->id
        ]);

        $order->items()->attach(
            $submittedItems->mapWithKeys(
                fn ($item) => [
                    $item->id => [
                        'price' => $item->price,
                        'quantity' => array_column($data['items'], 'quantity', 'id')[$item->id]
                    ]
                ]
            )
        );

        return response()->json($order, HttpStatus::CREATED->value);
    }
}
