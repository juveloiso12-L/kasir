<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransPayment
{
    public function createSnapToken(Order $order, string $customerName, string $customerEmail): string
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $order->loadMissing('items');

        return Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $order->order_id,
                'gross_amount' => $order->gross_amount,
            ],
            'item_details' => $order->items->map(fn (OrderItem $item): array => [
                'id' => $item->product_id,
                'price' => $item->unit_price,
                'quantity' => $item->quantity,
                'name' => $item->product_name,
            ])->all(),
            'customer_details' => [
                'first_name' => $customerName,
                'email' => $customerEmail,
            ],
        ]);
    }
}
