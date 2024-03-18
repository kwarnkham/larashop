<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Enums\PaymentType;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'type' => ['required', Rule::in(PaymentType::all())],
        ]);

        $order = Order::query()->find($data['order_id']);

        $data['amount'] = $order->amount;

        $payment = $order->payment()->create($data)->fresh();

        return response()->json($payment, HttpStatus::CREATED->value);
    }
}
