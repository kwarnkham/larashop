<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $paymentResponse, public int $paymentId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payment = Payment::query()->find($this->paymentId);
        $payment->process($this->paymentResponse);
    }
}
