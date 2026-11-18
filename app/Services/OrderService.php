<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @param  array<int, array{product_id: string, quantity: int}>  $items
     */
    public function create(array $items, int $discount, string $paymentMethod, string $status): Order
    {
        $productSlugs = collect($items)->pluck('product_id');
        $products = Product::query()->whereIn('slug', $productSlugs)->get()->keyBy('slug');

        foreach ($productSlugs->unique() as $productSlug) {
            if (! $products->has($productSlug)) {
                throw ValidationException::withMessages([
                    'items' => 'Satu atau lebih produk tidak tersedia.',
                ]);
            }
        }

        $subtotal = collect($items)->sum(
            fn (array $item): int => $products[$item['product_id']]->price * $item['quantity'],
        );

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount' => 'Diskon tidak boleh melebihi subtotal.',
            ]);
        }

        return DB::transaction(function () use ($items, $discount, $paymentMethod, $status, $products, $subtotal): Order {
            $order = Order::create([
                'order_id' => 'ORD-'.Str::ulid(),
                'status' => $status,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'gross_amount' => $subtotal - $discount,
            ]);

            foreach ($items as $item) {
                $product = $products[$item['product_id']];
                $order->items()->create([
                    'product_id' => $product->slug,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'line_total' => $product->price * $item['quantity'],
                ]);
            }

            return $order->load('items');
        });
    }
}
