<?php

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OrderPaid;
use App\Services\Larapay;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    public function test_process_payment(): void
    {
        Notification::fake();
        $user = User::factory()->create()->fresh();
        $payment = Payment::factory()
            ->for(
                Order::factory()
                    ->hasAttached(
                        Item::factory()->state(['price' => 1])->count(2),
                        ['quantity' => 1, 'price' => 1]
                    )->state(['user_id' => $user->id]),
                'payable'
            )->create();

        $this->assertEquals($payment->type, PaymentType::Larapay);

        $paymentResponse = Larapay::mockResponse($payment);

        $payment->process($paymentResponse);

        $this->assertTrue($payment->refresh()->status == PaymentStatus::Completed);

        $this->assertEquals(
            $payment->refresh()->result,
            json_decode($paymentResponse, associative: true)
        );
        Notification::assertSentTo($user, OrderPaid::class);
    }
}
