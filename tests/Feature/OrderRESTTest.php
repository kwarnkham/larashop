<?php

namespace Tests\Feature;

use App\Http\Controllers\OrderController;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
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

    public function test_list_orders(): void
    {
        Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->count(30)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/orders');
        $response->assertOk();
        $response->assertJsonCount(OrderController::PER_PAGE, 'pagination.data');
    }

    public function test_find_an_order(): void
    {
        Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->count(30)->create(['user_id' => $this->user->id]);

        $order = Order::query()->inRandomOrder()->first();
        $response = $this->getJson('/api/orders/' . $order->id);
        $response->assertOk();
        $this->assertEquals($order->id, $response->json()['id']);
    }

    public function test_update_an_order_status(): void
    {
        $order = Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->create(['user_id' => $this->user->id]);

        $data = [
            'status' => 'confirmed'
        ];

        $response = $this->actingAs($this->user)->putJson('/api/orders/' . $order->id, $data);
        $response->assertOk();

        $order->refresh();

        $this->assertEquals($response->json()['status'], $data['status']);
    }
}
