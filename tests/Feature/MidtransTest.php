<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Services\MidtransPayment;
use App\Services\OrderService;
use App\Services\ReceiptPrinter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class MidtransTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_checkout_creates_pending_order_and_returns_snap_token(): void
    {
        Product::factory()->create([
            'slug' => 'americano',
            'price' => 15000,
        ]);
        config([
            'midtrans.server_key' => 'sandbox-server-key',
            'midtrans.client_key' => 'sandbox-client-key',
        ]);
        $this->mock(MidtransPayment::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createSnapToken')->once()->andReturn('snap-token');
        });

        $response = $this->postJson(route('midtrans.checkout'), [
            'items' => [
                ['product_id' => 'americano', 'quantity' => 2],
            ],
            'customer_name' => 'Test Buyer',
            'customer_email' => 'buyer@example.com',
        ]);

        $response->assertCreated()
            ->assertJsonPath('snap_token', 'snap-token');
        $this->assertDatabaseHas('orders', [
            'order_id' => $response->json('order_id'),
            'status' => 'pending',
            'payment_method' => 'midtrans',
            'gross_amount' => 30000,
            'snap_token' => 'snap-token',
        ]);
    }

    public function test_checkout_requires_midtrans_server_key(): void
    {
        Product::factory()->create([
            'slug' => 'americano',
            'price' => 15000,
        ]);
        config([
            'midtrans.server_key' => null,
            'midtrans.client_key' => null,
        ]);

        $response = $this->postJson(route('midtrans.checkout'), [
            'items' => [
                ['product_id' => 'americano', 'quantity' => 1],
            ],
            'customer_name' => 'Test Buyer',
            'customer_email' => 'buyer@example.com',
        ]);

        $response->assertServiceUnavailable();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_notification_rejects_an_invalid_signature(): void
    {
        config(['midtrans.server_key' => 'sandbox-server-key']);
        $product = Product::factory()->create([
            'slug' => 'americano',
            'price' => 15000,
        ]);
        $order = app(OrderService::class)->create(
            [['product_id' => $product->slug, 'quantity' => 1]],
            0,
            'midtrans',
            'pending',
        );

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $order->order_id,
            'status_code' => '200',
            'gross_amount' => '15000.00',
            'signature_key' => 'invalid',
            'transaction_status' => 'settlement',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_settlement_notification_marks_order_paid_and_prints_once(): void
    {
        $serverKey = 'sandbox-server-key';
        config(['midtrans.server_key' => $serverKey]);
        $product = Product::factory()->create([
            'slug' => 'americano',
            'price' => 15000,
        ]);
        $order = app(OrderService::class)->create(
            [['product_id' => $product->slug, 'quantity' => 1]],
            0,
            'midtrans',
            'pending',
        );
        $grossAmount = '15000.00';
        $this->mock(ReceiptPrinter::class, function (MockInterface $mock): void {
            $mock->shouldReceive('print')->once();
        });

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $order->order_id,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $order->order_id.'200'.$grossAmount.$serverKey),
            'transaction_status' => 'settlement',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull(Order::findOrFail($order->id)->receipt_printed_at);
    }
}
