<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;

class Larapay implements PaymentService
{
    public function __construct(public array $data)
    {
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
