<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;

interface PaymentService
{
    public function verifySign(): bool;

    public function getSign(array $data): string;

    public static function mockResponse(Payment $payment): string;

    public function getStatus(): PaymentStatus;

    // public static function requestPayment(Payment $payment);

}
