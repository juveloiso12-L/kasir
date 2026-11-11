<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\ReceiptPrinter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cash_checkout_saves_order_and_prints_receipt(): void
    {
        $this->mock(ReceiptPrinter::class, function (MockInterface $mock): void {
            $mock->shouldReceive('print')->once();
        });

        $response = $this->postJson(route('checkout.store'), [
            'items' => [
                ['product_id' => 'kopi-susu', 'quantity' => 2],
            ],
            'discount' => 6000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'paid');
        $this->assertStringStartsWith('ORD-', $response->json('order_id'));
        $this->assertStringContainsString('ORD-', $response->json('receipt_url'));

        $order = Order::query()->with('items')->firstOrFail();
        $this->assertModelExists($order);
        $this->assertSame(36000, $order->subtotal);
        $this->assertSame(6000, $order->discount);
        $this->assertSame(30000, $order->gross_amount);
        $this->assertNotNull($order->receipt_printed_at);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => 'kopi-susu',
            'quantity' => 2,
            'unit_price' => 18000,
            'line_total' => 36000,
        ]);
    }

    public function test_invalid_product_does_not_create_order(): void
    {
        $response = $this->postJson(route('checkout.store'), [
            'items' => [
                ['product_id' => 'unknown', 'quantity' => 1],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_id']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_discount_cannot_exceed_subtotal(): void
    {
        $response = $this->postJson(route('checkout.store'), [
            'items' => [
                ['product_id' => 'americano', 'quantity' => 1],
            ],
            'discount' => 15001,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['discount']);
        $this->assertDatabaseCount('orders', 0);
    }
}
