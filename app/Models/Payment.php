<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Jobs\ProcessPayment;
use App\Services\Larapay;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'type' => PaymentType::class,
            'result' => 'array'
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Payment $payment) {
            $payment->refresh();
            if ($payment->status === PaymentStatus::Pending) {
                if ($payment->type === PaymentType::Larapay) {
                    ProcessPayment::dispatch(
                        Larapay::mockResponse($payment),
                        $payment->id
                    )->delay(10);
                }
            }
        });
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function getService($data): PaymentService
    {
        if ($this->type == PaymentType::Larapay) {
            return new Larapay($data);
        }
    }

    public function process(string $paymentResponse): bool
    {
        $data = json_decode($paymentResponse, associative: true);
        $paymentService = $this->getService($data);
        if (!$paymentService->verifySign()) return false;
        return $this->update([
            'result' => $data,
            'status' => $paymentService->getStatus()
        ]);
    }
}
