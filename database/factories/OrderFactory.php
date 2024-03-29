<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            $items = Item::factory()->count(fake()->numberBetween(1, 3))->create();
            $order->items()->attach($items->mapWithKeys(function ($item) {
                return [$item->id => ['price' => $item->price, 'quantity' => fake()->numberBetween(1, 10)]];
            }));
            $address = Address::factory()->create(['user_id' => $order->user_id]);
            $order->update(['address_id' => $address->id]);
        });
    }
}
