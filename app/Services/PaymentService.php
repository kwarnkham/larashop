<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;

interface PaymentService
{
    const PAYMENT_URL = 'payment url';

    const KEY = 'key';

    const MERCHANT_ID = 'mechant id';

    public function verifySign(): bool;

    public function getSign(array $data): string;

    public static function mockResponse(Payment $payment): string;

    public function getStatus(): PaymentStatus;

    public function requestPaymentUrl(): string;

    public static function createForRequest($referenceId, $amount): self;
}
