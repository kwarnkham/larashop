<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Jobs\ProcessPayment;
use App\Models\Payment;
use App\Services\Larapay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentServiceController extends Controller
{
    public function handle(Request $request, Payment $payment)
    {
        return $payment->handlePaymentServiceResponse(json_encode($request->all()));
    }
}
