<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Services\MidtransPayment;
use App\Services\OrderService;
use App\Services\ReceiptPrinter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public function checkout(
        CreateOrderRequest $request,
        OrderService $orders,
        MidtransPayment $payments,
    ): JsonResponse {
        abort_if(
            blank(config('midtrans.server_key')) || blank(config('midtrans.client_key')),
            503,
            'MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY belum dikonfigurasi.',
        );

        $customer = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
        ]);
        $orderData = $request->validated();
        $order = $orders->create(
            $orderData['items'],
            (int) ($orderData['discount'] ?? 0),
            'midtrans',
            'pending',
        );
        $snapToken = $payments->createSnapToken(
            $order,
            $customer['customer_name'],
            $customer['customer_email'],
        );
        $order->forceFill(['snap_token' => $snapToken])->save();

        return response()->json([
            'order_id' => $order->order_id,
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
        ], 201);
    }

    public function notification(Request $request, ReceiptPrinter $receiptPrinter): JsonResponse
    {
        $notification = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'numeric'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'fraud_status' => ['nullable', 'string'],
        ]);
        $serverKey = config('midtrans.server_key');

        abort_if(blank($serverKey), 503, 'MIDTRANS_SERVER_KEY belum dikonfigurasi.');

        $expectedSignature = hash(
            'sha512',
            $notification['order_id'].$notification['status_code'].$request->input('gross_amount').$serverKey,
        );
        abort_unless(hash_equals($expectedSignature, $notification['signature_key']), 403, 'Signature Midtrans tidak valid.');

        $order = Order::query()->where('order_id', $notification['order_id'])->firstOrFail();
        $receivedAmount = number_format((float) $request->input('gross_amount'), 2, '.', '');
        $expectedAmount = number_format((float) $order->gross_amount, 2, '.', '');
        abort_unless(hash_equals($expectedAmount, $receivedAmount), 403, 'Nominal pembayaran tidak sesuai.');

        $transactionStatus = $notification['transaction_status'];
        $shouldPrintReceipt = false;

        if (
            $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && ($notification['fraud_status'] ?? null) === 'accept')
        ) {
            $order->status = 'paid';
            $shouldPrintReceipt = $order->receipt_printed_at === null;
        } elseif ($transactionStatus === 'pending' && $order->status !== 'paid') {
            $order->status = 'pending';
        } elseif (
            in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)
            && $order->status !== 'paid'
        ) {
            $order->status = 'failed';
        } elseif (in_array($transactionStatus, ['refund', 'partial_refund'], true)) {
            $order->status = 'refunded';
        }

        $order->save();

        if ($shouldPrintReceipt) {
            $receiptPrinter->print($order);
            $order->forceFill(['receipt_printed_at' => now()])->save();
        }

        return response()->json(['message' => 'Notifikasi pembayaran diproses.']);
    }
}
