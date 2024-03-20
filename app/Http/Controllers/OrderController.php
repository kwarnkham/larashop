<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Enums\PaymentType;
use App\Http\Requests\SubmitOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    const PER_PAGE = 20;

    public function store(SubmitOrderRequest $request)
    {
        $data = $request->validated();

        $submittedItems = $request->getSubmittedItems();

        $user = $request->user();

        $order = Order::query()->create([
            'user_id' => $user->id,
        ]);

        $order->saveItems($submittedItems, $data);

        return response()->json($order, HttpStatus::CREATED->value);
    }

    public function index(Request $request)
    {
        $query = Order::query();

        return response()
            ->json(
                [
                    'pagination' => $query->paginate($request->per_page ?? OrderController::PER_PAGE),
                ],
                HttpStatus::OK->value
            );
    }

    public function find(Request $request, Order $order)
    {
        return response()->json($order);
    }

    public function update(UpdateOrderStatusRequest $request, Order $order)
    {
        $data = $request->validated();

        $order->update($data);

        return response()->json($order);
    }

    public function updateOrderItem(SubmitOrderRequest $request, Order $order)
    {
        abort_unless($request->user()->id == $order->user_id, HttpStatus::FORBIDDEN->value);

        $data = $request->validated();

        $submittedItems = $request->getSubmittedItems();

        $order->saveItems($submittedItems, $data);

        return response()->json($order);
    }

    public function pay(Request $request, Order $order)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(PaymentType::all())],
        ]);

        $payment = $order->payment()->create([
            'type' => $data['type'],
            'amount' => $order->amount,
        ]);

        $paymentUrl = $payment->requestPaymentUrl();

        return response()->json($paymentUrl, HttpStatus::OK->value);
    }
}
