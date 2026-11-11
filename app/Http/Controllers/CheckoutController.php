<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Services\OrderService;
use App\Services\ReceiptPrinter;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function store(
        CreateOrderRequest $request,
        OrderService $orders,
        ReceiptPrinter $receiptPrinter,
    ): JsonResponse {
        $validated = $request->validated();
        $order = $orders->create(
            $validated['items'],
            (int) ($validated['discount'] ?? 0),
            'cash',
            'paid',
        );

        $receiptPrinter->print($order);
        $order->forceFill(['receipt_printed_at' => now()])->save();

        return response()->json([
            'order_id' => $order->order_id,
            'status' => $order->status,
            'receipt_url' => route('orders.receipt', $order),
        ], 201);
    }
}
