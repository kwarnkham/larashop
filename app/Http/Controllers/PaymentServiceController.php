<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentServiceController extends Controller
{
    public function handle(Request $request, Payment $payment)
    {
        return $payment->handlePaymentServiceResponse(json_encode($request->all()));
    }
}
