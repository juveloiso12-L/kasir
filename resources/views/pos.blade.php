<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $storeName }} | POS</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, sans-serif; color: #24211e; background: #f5f2ec; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        header { padding: 28px max(24px, calc((100% - 1080px) / 2)); color: #fffaf2; background: #30251d; }
        header p { margin: 4px 0 0; color: #d7c6ae; }
        main { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr); gap: 24px; max-width: 1080px; margin: 32px auto; padding: 0 20px; }
        section { padding: 24px; border: 1px solid #e7dfd4; border-radius: 16px; background: #fff; box-shadow: 0 8px 24px #30251d0a; }
        h1, h2, p { margin-top: 0; }
        h1 { margin-bottom: 0; font-size: 24px; }
        h2 { font-size: 18px; }
        .products { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
        .product { padding: 16px; border: 1px solid #e7dfd4; border-radius: 12px; background: #fffaf4; }
        .product strong, .product span { display: block; }
        .product span { margin-top: 6px; color: #76695d; }
        button, input, select { font: inherit; }
        button { cursor: pointer; }
        .add, .primary { border: 0; border-radius: 9px; color: white; background: #7a4c2b; }
        .add { width: 100%; margin-top: 12px; padding: 9px; }
        .primary { padding: 12px 14px; }
        .secondary { padding: 11px 14px; border: 1px solid #7a4c2b; border-radius: 9px; color: #603a21; background: #fff; }
        button:disabled { cursor: not-allowed; opacity: .5; }
        .cart-row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 0; border-bottom: 1px solid #eee8df; }
        .cart-row small { display: block; margin-top: 4px; color: #76695d; }
        .remove { border: 0; color: #954a34; background: transparent; }
        .totals { margin: 18px 0; }
        .totals div { display: flex; justify-content: space-between; margin: 8px 0; }
        label { display: block; margin: 14px 0 6px; font-size: 14px; font-weight: 600; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #d8cfc4; border-radius: 8px; }
        .actions { display: grid; gap: 10px; }
        #message { min-height: 24px; margin: 14px 0 0; color: #286344; }
        #message.error { color: #a63e32; }
        .empty { color: #76695d; }
        @media (max-width: 760px) { main { grid-template-columns: 1fr; margin-top: 20px; } }
    </style>
</head>
<body>
    <header>
        <h1>{{ $storeName }}</h1>
        <p>Point of Sale - pesanan dan pembayaran</p>
    </header>
    <main>
        <section>
            <h2>Menu</h2>
            <div class="products">
                @foreach ($products as $id => $product)
                    <article class="product">
                        <strong>{{ $product['name'] }}</strong>
                        <span>Rp{{ number_format($product['price'], 0, ',', '.') }}</span>
                        <button class="add" type="button" data-product="{{ $id }}">Tambah</button>
                    </article>
                @endforeach
            </div>
        </section>
        <section>
            <h2>Keranjang</h2>
            <div id="cart" class="empty">Belum ada item.</div>
            <label for="discount">Diskon (Rp)</label>
            <input id="discount" type="number" min="0" step="1000" value="0">
            <label for="customer-name">Nama pembeli (pembayaran online)</label>
            <input id="customer-name" type="text" maxlength="255" autocomplete="name">
            <label for="customer-email">Email pembeli (pembayaran online)</label>
            <input id="customer-email" type="email" maxlength="255" autocomplete="email">
            <div class="totals">
                <div><span>Subtotal</span><strong id="subtotal">Rp0</strong></div>
                <div><span>Diskon</span><strong id="discount-total">Rp0</strong></div>
                <div><span>Total</span><strong id="total">Rp0</strong></div>
            </div>
            <div class="actions">
                <button id="cash" class="primary" type="button">Simpan & cetak struk</button>
                @if (filled(config('midtrans.client_key')) && filled(config('midtrans.server_key')))
                    <button id="online" class="secondary" type="button">Bayar dengan Midtrans</button>
                @else
                    <button class="secondary" type="button" disabled>Midtrans (atur kunci di .env)</button>
                @endif
            </div>
            <p id="message" role="status" aria-live="polite"></p>
        </section>
    </main>
    <script>
        const products = @js($products);
        const cart = new Map();
        const formatter = new Intl.NumberFormat('id-ID');
        const message = document.getElementById('message');
        let isSubmitting = false;

        function rupiah(value) {
            return `Rp${formatter.format(value)}`;
        }

        function renderCart() {
            const element = document.getElementById('cart');
            const items = [...cart.entries()];

            if (items.length === 0) {
                element.className = 'empty';
                element.textContent = 'Belum ada item.';
            } else {
                element.className = '';
                element.replaceChildren(...items.map(([id, quantity]) => {
                    const product = products[id];
                    const row = document.createElement('div');
                    row.className = 'cart-row';
                    const details = document.createElement('div');
                    const name = document.createElement('span');
                    name.textContent = product.name;
                    const summary = document.createElement('small');
                    summary.textContent = `${quantity} x ${rupiah(product.price)}`;
                    details.append(name, summary);

                    const lineTotal = document.createElement('strong');
                    lineTotal.textContent = rupiah(product.price * quantity);

                    const removeButton = document.createElement('button');
                    removeButton.className = 'remove';
                    removeButton.type = 'button';
                    removeButton.dataset.remove = id;
                    removeButton.setAttribute('aria-label', `Hapus ${product.name}`);
                    removeButton.textContent = 'Hapus';

                    row.append(details, lineTotal, removeButton);

                    return row;
                }));
            }

            const subtotal = items.reduce((sum, [id, quantity]) => sum + products[id].price * quantity, 0);
            const discount = Math.min(Math.max(0, Number(document.getElementById('discount').value) || 0), subtotal);
            document.getElementById('subtotal').textContent = rupiah(subtotal);
            document.getElementById('discount-total').textContent = rupiah(discount);
            document.getElementById('total').textContent = rupiah(subtotal - discount);
            document.getElementById('cash').disabled = isSubmitting || items.length === 0;
            const online = document.getElementById('online');
            if (online) online.disabled = isSubmitting || items.length === 0;
            document.querySelectorAll('[data-product]').forEach((button) => {
                button.disabled = isSubmitting;
            });
            document.getElementById('discount').disabled = isSubmitting;
            document.getElementById('customer-name').disabled = isSubmitting;
            document.getElementById('customer-email').disabled = isSubmitting;
            element.querySelectorAll('[data-remove]').forEach((button) => {
                button.disabled = isSubmitting;
            });
        }

        function finishPayment(text, { isError = false, clearOrder = false } = {}) {
            if (clearOrder) {
                cart.clear();
                document.getElementById('discount').value = 0;
            }

            isSubmitting = false;
            message.className = isError ? 'error' : '';
            message.textContent = text;
            renderCart();
        }

        async function sendOrder(url, online = false) {
            if (isSubmitting || cart.size === 0) return;

            isSubmitting = true;
            renderCart();
            message.className = '';
            message.textContent = 'Memproses transaksi...';
            let paymentModalOpen = false;
            let paymentFinished = false;

            const payload = {
                items: [...cart.entries()].map(([product_id, quantity]) => ({ product_id, quantity })),
                discount: Number(document.getElementById('discount').value) || 0,
            };

            if (online) {
                payload.customer_name = document.getElementById('customer-name').value;
                payload.customer_email = document.getElementById('customer-email').value;
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Transaksi gagal diproses.');
                }

                if (online) {
                    paymentModalOpen = true;
                    window.snap.pay(data.snap_token, {
                        onSuccess: () => {
                            if (paymentFinished) return;
                            paymentFinished = true;
                            finishPayment(`Pembayaran ${data.order_id} berhasil. Status akhir dikonfirmasi oleh Midtrans.`, { clearOrder: true });
                        },
                        onPending: () => {
                            if (paymentFinished) return;
                            paymentFinished = true;
                            finishPayment(`Pembayaran ${data.order_id} menunggu konfirmasi.`, { clearOrder: true });
                        },
                        onError: () => {
                            if (paymentFinished) return;
                            paymentFinished = true;
                            finishPayment('Pembayaran gagal. Coba kembali.', { isError: true });
                        },
                        onClose: () => {
                            if (paymentFinished) return;
                            paymentFinished = true;
                            finishPayment(`Pembayaran ${data.order_id} belum diselesaikan.`);
                        },
                    });
                    return;
                }

                message.replaceChildren(document.createTextNode(`Transaksi ${data.order_id} tersimpan. `));
                const receiptLink = document.createElement('a');
                receiptLink.href = data.receipt_url;
                receiptLink.textContent = 'Unduh struk PDF';
                message.append(receiptLink);
                cart.clear();
                document.getElementById('discount').value = 0;
            } catch (error) {
                paymentModalOpen = false;
                message.className = 'error';
                message.textContent = error.message;
            } finally {
                if (!paymentModalOpen) {
                    isSubmitting = false;
                    renderCart();
                }
            }
        }

        document.querySelectorAll('[data-product]').forEach((button) => {
            button.addEventListener('click', () => {
                const id = button.dataset.product;
                cart.set(id, (cart.get(id) || 0) + 1);
                renderCart();
            });
        });
        document.getElementById('cart').addEventListener('click', (event) => {
            const id = event.target.dataset.remove;
            if (!id) return;
            cart.delete(id);
            renderCart();
        });
        document.getElementById('discount').addEventListener('input', renderCart);
        document.getElementById('cash').addEventListener('click', () => sendOrder(@js(route('checkout.store'))));
        const onlineButton = document.getElementById('online');
        if (onlineButton) onlineButton.addEventListener('click', () => sendOrder(@js(route('midtrans.checkout')), true));
    </script>
    @if (filled(config('midtrans.client_key')) && filled(config('midtrans.server_key')))
        <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif
</body>
</html>
