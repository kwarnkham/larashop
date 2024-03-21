<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentType;
use App\Http\Controllers\OrderController;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderRESTTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::query()->whereRelation('roles', 'name', 'admin')->first();
        $this->user = User::factory()->create();
        Event::fake([BroadcastableModelEventOccurred::class]);
    }

    public function test_user_can_update_order_item()
    {
        $order = Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->create(['user_id' => $this->user->id]);

        $this->assertDatabaseCount('item_order', 2);

        $items = Item::factory()->count(10)->create();

        $data = [
            'items' => $items->map(
                fn ($item) => ['id' => $item->id, 'quantity' => fake()->numberBetween(1, 5)]
            )->toArray(),
        ];

        $response = $this
            ->actingAs($this->user)
            ->postJson("api/orders/{$order->id}/update", $data);

        $response->assertOk();

        $this->assertDatabaseCount('item_order', 10);
    }

    public function test_only_owner_or_admin_can_update_order()
    {
        $order = Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->create(['user_id' => $this->user->id]);

        $data = [
            'status' => OrderStatus::Canceled,
        ];
        /** @var \Illuminate\Contracts\Auth\Authenticatable $anotherUser */
        $anotherUser = User::factory()->create();
        $response = $this->actingAs($anotherUser)->putJson('/api/orders/'.$order->id, $data);
        $response->assertForbidden();

        $response = $this->actingAs($this->user)->putJson('/api/orders/'.$order->id, $data);
        $response->assertOk();

        $response = $this->actingAs($this->admin)->putJson('/api/orders/'.$order->id, $data);
        $response->assertOk();
    }

    public function test_owner_can_only_update_order_status_to_canceled()
    {
        $order = Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->create(['user_id' => $this->user->id]);

        $this->assertEquals($order->fresh()->status, OrderStatus::Pending);

        $data = [
            'status' => OrderStatus::Paid,
        ];

        $response = $this->actingAs($this->user)->putJson('/api/orders/'.$order->id, $data);
        $response->assertForbidden();

        $data = [
            'status' => OrderStatus::Canceled,
        ];

        $response = $this->actingAs($this->user)->putJson('/api/orders/'.$order->id, $data);
        $response->assertOk();

        $this->assertEquals($order->fresh()->status, $data['status']);
    }

    public function test_create_an_order(): void
    {
        $items = Item::factory()->count(10)->create();

        $data = [
            'items' => $items->map(
                fn ($item) => ['id' => $item->id, 'quantity' => fake()->numberBetween(1, 5)]
            )->toArray(),
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
        $response = $this->getJson('/api/orders/'.$order->id);
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
            'status' => OrderStatus::Confirmed,
        ];

        $response = $this->actingAs($this->admin)->putJson('/api/orders/'.$order->id, $data);
        $response->assertOk();

        $order->refresh();

        $this->assertEquals($response->json()['status'], $data['status']->value);
    }

    public function test_user_pays_for_an_order(): void
    {
        $order = Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->create(['user_id' => $this->user->id]);

        $data = [
            'type' => PaymentType::Larapay,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/orders/'.$order->id.'/pay', $data);

        $response->assertOk();

        $this->assertDatabaseCount('payments', 1);
    }
}
