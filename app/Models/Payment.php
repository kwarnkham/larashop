<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Jobs\ProcessPayment;
use App\Notifications\OrderPaid;
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
            'result' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Payment $payment) {
            $payment->refresh();
            //mocking responding to payment service callback to /api/payment-services/{payment}
            if ($payment->type === PaymentType::Larapay) {
                $payment->handlePaymentServiceResponse(Larapay::mockResponse($payment));
            }
        });

        static::updated(function (Payment $payment) {
            if ($payment->status == PaymentStatus::Completed) {
                $payment->payable->user->notify(new OrderPaid($payment->payable->id));
            }
        });
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function getService(?array $data = null): PaymentService
    {
        if ($this->type == PaymentType::Larapay) {
            $merchantId = 'merchantId'; //should comes from config set via env
            $key = 'key'; //should comes from config set via env
            if ($data != null) {
                $valid = array_key_exists('reference_id', $data)
                    && array_key_exists('id', $data)
                    && array_key_exists('amount', $data)
                    && array_key_exists('status', $data)
                    && array_key_exists('sign', $data)
                    && array_key_exists('paid_at', $data);
            } else {
                return Larapay::createForRequest($this->id, $this->amount);
            }
            if (! $valid) {
                return null;
            }

            return new Larapay(
                referenceId: $data['reference_id'],
                id: $data['id'],
                amount: $data['amount'],
                status: $data['status'],
                sign: $data['sign'],
                paidAt: $data['paid_at']
            );
        }
    }

    public function process(string $paymentResponse): bool
    {
        $data = json_decode($paymentResponse, associative: true);
        $paymentService = $this->getService($data);
        if (! $paymentService) {
            return false;
        }
        if (! $paymentService->verifySign()) {
            return false;
        }

        return $this->update([
            'result' => $data,
            'status' => $paymentService->getStatus(),
        ]);
    }

    public function requestPaymentUrl(): string
    {
        $paymentService = $this->getService();

        return $paymentService->requestPaymentUrl();
    }

    public function handlePaymentServiceResponse(string $paymentServiceResponse)
    {
        if ($this->status === PaymentStatus::Pending) {
            ProcessPayment::dispatch(
                $paymentServiceResponse,
                $this->id
            );
        } else {
            // payment services usually recommend you to return a signal to confirm that you have handled the request so that they can stop hitting this controller again

            // assuming we handled the response successfully if the status is not pending
            return 'SUCCESS';
        }
    }
}
