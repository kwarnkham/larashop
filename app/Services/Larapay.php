<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Payment;
use Str;

class Larapay implements PaymentService
{
    public function __construct(public array $data)
    {
    }

    public static function mockResponse(Payment $payment): string
    {
        $data = [
            'reference_id' => $payment->id,
            'id' => time() . Str::random(6),
            'amount' => $payment->amount,
            'paid_at' =>  time() * 1000,
            'status' => 1,
        ];
        ksort($data);
        $key = PaymentType::Larapay->value;
        $sign = md5(http_build_query($data) . "&key={$key}");
        $data['sign'] = $sign;
        return json_encode($data);
    }

    public function getStatus(): PaymentStatus
    {
        switch ($this->data['status']) {
            case 1:
                return PaymentStatus::Completed;
            case 2:
                return PaymentStatus::Processing;
            case 3:
                return PaymentStatus::Canceled;
            default:
                return PaymentStatus::Pending;
        }
    }

    public function verifySign(): bool
    {
        $data = array_filter($this->data, fn ($key) => $key != 'sign', ARRAY_FILTER_USE_KEY);
        ksort($data);
        //mocking key
        $key = PaymentType::Larapay->value;
        $sign = md5(http_build_query($data) . "&key={$key}");
        return $sign == $this->data['sign'];
    }
}
