<?php

namespace Tests\Feature;

use App\Jobs\ProcessPayment;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Larapay;
use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_respone_from_payment_service(): void
    {
        Queue::fake([ProcessPayment::class]);
        Event::fake([BroadcastableModelEventOccurred::class]);
        $payment = Payment::factory()
            ->for(
                Order::factory()
                    ->hasAttached(
                        Item::factory()->state(['price' => 1])->count(2),
                        ['quantity' => 1, 'price' => 1]
                    )->for(User::factory()),
                'payable'
            )->create();

        $response = $this->postJson(
            'api/payment-services/'.$payment->id,
            json_decode(Larapay::mockResponse($payment), associative: true)
        );

        $response->assertOk();
        Queue::assertPushed(ProcessPayment::class);
    }
}
