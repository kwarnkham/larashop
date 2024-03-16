<?php

namespace App\Services;

use App\Enums\PaymentStatus;

interface PaymentService
{
    public function verifySign(): bool;

    public function getStatus(): PaymentStatus;
}
