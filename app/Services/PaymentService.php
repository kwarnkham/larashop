<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;

interface PaymentService
{
    public function verifySign(): bool;

    public static function mockResponse(Payment $payment): string;

    public function getStatus(): PaymentStatus;
}
