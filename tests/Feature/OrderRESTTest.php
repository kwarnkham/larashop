<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Controllers\OrderController;
use App\Models\Address;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_user_can_download_receipt_of_an_order()
    {
        $order = Order::factory()
            ->hasAttached(
                Item::factory()->count(2),
                ['quantity' => 2, 'price' => 1000]
            )->has(Payment::factory()->state(['amount' => 4000, 'status' => PaymentStatus::Completed]))->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->postJson("api/orders/{$order->id}/receipt");

        $response->assertDownload();
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
        $response = $this->actingAs($this->admin)->getJson('/api/orders');
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
        $response = $this->actingAs($this->admin)->getJson('/api/orders/'.$order->id);
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

    public function test_set_address_to_an_order(): void
    {
        $order = Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->create(['user_id' => $this->user->id]);

        $address = Address::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)->postJson('/api/orders/'.$order->id.'/set-address', ['address_id' => $address->id]);

        $response->assertOk();

        $order->refresh();

        $this->assertEquals($order->address_id, $address->id);
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

    public function test_user_can_only_view_owned_orders(): void
    {
        $order = Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->for(User::factory())->create();

        $response = $this->actingAs($this->user)->getJson('/api/orders');

        $response->assertOk();

        $response->assertJsonCount(0, 'pagination.data');

        $response = $this->actingAs($this->user)->getJson("/api/orders/{$order->id}");

        $response->assertForbidden();

        $order = Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/orders');

        $response->assertOk();

        $response->assertJsonCount(1, 'pagination.data');

        $response = $this->actingAs($this->user)->getJson("/api/orders/{$order->id}");

        $response->assertOk();
    }

    public function test_date_filtering_orders(): void
    {
        Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->for(User::factory())->create(['updated_at' => now()]);

        Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->for(User::factory())->create(['updated_at' => now()->addDay()]);

        Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => 1, 'price' => 1]
        )->for(User::factory())->create(['updated_at' => now()->addDays(2)]);

        $query = http_build_query([
            'from' => now()->subDay()->toDateString(),
            'to' => now()->subDay()->toDateString()]);
        $this->actingAs($this->admin)->getJson('/api/orders?'.$query)->assertJsonCount(0, 'pagination.data');

        $query = http_build_query([
            'from' => now()->toDateString(),
            'to' => now()->toDateString()]);
        $this->actingAs($this->admin)->getJson('/api/orders?'.$query)->assertJsonCount(1, 'pagination.data');

        $query = http_build_query([
            'from' => now()->toDateString(),
            'to' => now()->addDay()->toDateString()]);
        $this->actingAs($this->admin)->getJson('/api/orders?'.$query)->assertJsonCount(2, 'pagination.data');

        $query = http_build_query([
            'from' => now()->toDateString(),
            'to' => now()->addDays(2)->toDateString()]);
        $this->actingAs($this->admin)->getJson('/api/orders?'.$query)->assertJsonCount(3, 'pagination.data');
    }

    public function test_amount_report_of_orders(): void
    {
        Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => fake()->numberBetween(1, 10), 'price' => fake()->numberBetween(100, 1000)]
        )->for(User::factory())->create(['updated_at' => now()]);

        Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => fake()->numberBetween(1, 10), 'price' => fake()->numberBetween(100, 1000)]
        )->for(User::factory())->create(['updated_at' => now()->addDay()]);

        Order::factory()->hasAttached(
            Item::factory()->count(2),
            ['quantity' => fake()->numberBetween(1, 10), 'price' => fake()->numberBetween(100, 1000)]
        )->for(User::factory())->create(['updated_at' => now()->addDays(2)]);

        $query = http_build_query([
            'from' => now()->toDateString(),
            'to' => now()->addDays(2)->toDateString()]);

        $response = $this->actingAs($this->admin)->getJson('/api/orders?'.$query);

        $this->assertEquals(DB::table('item_order')->sum(DB::raw('price*quantity')), $response->json()['report']['amount']);
    }
}
