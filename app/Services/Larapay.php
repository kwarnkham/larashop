<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Payment;
use Str;

class Larapay implements PaymentService
{
    //mocking key, should comes from config set in env file
    const KEY = PaymentType::Larapay->value;

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
        $sign = md5(http_build_query($data) . "&key=" . static::KEY);
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
        $sign = md5(http_build_query($data) . "&key=" . static::KEY);
        return $sign == $this->data['sign'];
    }
}
