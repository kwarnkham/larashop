<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRESTTest extends TestCase
{

    use RefreshDatabase;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_create_an_order(): void
    {
        $items = Item::factory()->count(10)->create();

        $data =  [
            'items' => $items->map(
                fn ($item) => ['id' => $item->id, 'quantity' => fake()->numberBetween(1, 5)]
            )
        ];

        $response = $this->actingAs($this->user)->postJson('api/orders', $data);

        $response->assertCreated();

        $this->assertDatabaseCount('orders', 1);

        $this->assertEquals($this->user->id, $response->json()['user_id']);

        $this->assertDatabaseCount('item_order', $items->count());

        $items->each(function ($item) {
            $this->assertEquals(
                $item->price,
                ItemOrder::query()->where('item_id', $item->id)->first()->price
            );
        });

        foreach ($data['items'] as $key => $value) {
            $this->assertEquals(
                $value['quantity'],
                ItemOrder::query()->where('item_id', $value['id'])->first()->quantity
            );
        }
    }
}
