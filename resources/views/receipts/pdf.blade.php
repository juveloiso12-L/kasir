<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1, p { margin: 0 0 6px; text-align: center; }
        h1 { font-size: 14px; }
        .line { border-top: 1px dashed #555; margin: 8px 0; }
        .item { margin: 8px 0; }
        .row { display: flex; justify-content: space-between; margin: 4px 0; }
        .amount { text-align: right; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ config('pos.store_name') }}</h1>
    <p>{{ $order->order_id }}</p>
    <p>{{ $order->created_at->format('d/m/Y H:i:s') }}</p>
    <div class="line"></div>
    @foreach ($order->items as $item)
        <div class="item">
            <div>{{ $item->product_name }}</div>
            <div>{{ $item->quantity }} x Rp{{ number_format($item->unit_price, 0, ',', '.') }}</div>
            <div class="amount">Rp{{ number_format($item->line_total, 0, ',', '.') }}</div>
        </div>
    @endforeach
    <div class="line"></div>
    <div class="row"><span>Subtotal</span><span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
    <div class="row"><span>Diskon</span><span>Rp{{ number_format($order->discount, 0, ',', '.') }}</span></div>
    <div class="row total"><span>Total</span><span>Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</span></div>
    <div class="line"></div>
    <p>Terima kasih telah berbelanja.</p>
</body>
</html>
