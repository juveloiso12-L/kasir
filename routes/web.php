<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ReceiptController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pos', [
        'products' => Product::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Product $product): array => [
                $product->slug => [
                    'name' => $product->name,
                    'price' => $product->price,
                ],
            ])
            ->all(),
        'storeName' => config('pos.store_name'),
    ]);
})->name('pos');

Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/midtrans/checkout', [MidtransController::class, 'checkout'])->name('midtrans.checkout');
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');
Route::get('/orders/{order:order_id}/receipt', [ReceiptController::class, 'show'])->name('orders.receipt');
