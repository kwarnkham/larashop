<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Jobs\ProcessPayment;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_pay_for_an_order(): void
    {
        Queue::fake([ProcessPayment::class]);
        $price = fake()->numberBetween(1000, 10000);
        $order = Order::factory()->hasAttached(
            Item::factory()->count(2)->state(['price' => $price]),
            ['quantity' => 1, 'price' => $price]
        )->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson('api/payments', [
            'order_id' => $order->id,
            'type' => PaymentType::Larapay->value
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('payments', 1);
        $this->assertEquals($response->json()['status'], PaymentStatus::Pending->value);
        Queue::assertPushed(ProcessPayment::class);
    }
}
