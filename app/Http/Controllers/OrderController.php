<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Requests\SubmitOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
        ]);

        $user = $request->user();

        $filters = $request->only(['status', 'from', 'to']);

        if (! $user->hasRole('admin')) {
            $filters['user_id'] = $user->id;
        }

        $query = Order::query()->filter($filters);

        $report = [
            'amount' => $query->withSum('items as amount', DB::raw('item_order.price*item_order.quantity'))->pluck('amount')->sum(),
        ];

        return response()
            ->json(
                [
                    'pagination' => $query->paginate($request->per_page ?? OrderController::PER_PAGE),
                    'report' => $report,
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

        abort_unless($order->status == OrderStatus::Pending, HttpStatus::BAD_REQUEST->value, 'Can only update a pending order');

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

    public function downloadReceipt(Request $request, Order $order)
    {
        abort_unless(
            $order->payment && $order->payment->status == PaymentStatus::Completed, HttpStatus::BAD_REQUEST->value,
            'Order has not been paid yet');

        $pdf = Pdf::loadView('receipt.order',
            ['order' => $order->load(['payment', 'items', 'user'])->first()]);

        return $pdf->download(config('app')['name']." Receipt #{$order->id}.pdf");
    }

    public function setAddress(Request $request, Order $order)
    {
        $data = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
        ]);

        $order->update(['address_id' => $data['address_id']]);

        return response()->json($order, HttpStatus::OK->value);
    }
}
