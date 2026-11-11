<?php

namespace Tests\Feature;

use App\Services\OrderService;
use App\Services\ReceiptPrinter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReceiptPrinterTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_pdf_driver_saves_a_backend_generated_receipt(): void
    {
        Storage::fake('local');
        config(['pos.receipt.driver' => 'pdf']);
        $order = app(OrderService::class)->create(
            [['product_id' => 'roti-bakar', 'quantity' => 1]],
            2000,
            'cash',
            'paid',
        );

        app(ReceiptPrinter::class)->print($order);

        Storage::disk('local')->assertExists('receipts/'.$order->order_id.'.pdf');
        $this->get(route('orders.receipt', $order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
