@extends('layouts.app')

@section('title', 'Keranjang Belanja — MitraSpace')

@section('content')

<div class="page-header">
    <div class="container">
        <h1>Keranjang Belanja</h1>
        <div class="breadcrumb">
            <a href="{{ route('catalog.index') }}">Katalog</a>
            <span>›</span>
            <span>Keranjang</span>
        </div>
    </div>
</div>

<div class="container">
    @if(empty($cart))
    <div class="empty-state" style="padding: 100px 20px;">
        <svg viewBox="0 0 24 24" fill="none"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" stroke-width="1.5"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.5"/><path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="1.5"/></svg>
        <h3>Keranjang Masih Kosong</h3>
        <p>Belum ada produk yang ditambahkan. Yuk, mulai belanja!</p>
        <a href="{{ route('catalog.index') }}" class="btn btn-primary btn-lg">Mulai Belanja</a>
    </div>
    @else
    <div class="cart-layout">

        {{-- Cart Items --}}
        <div>
            <div class="cart-items">
                <div class="cart-header">
                    <h2>
                        <svg viewBox="0 0 24 24" fill="none" width="20" height="20" style="display:inline;vertical-align:middle;margin-right:6px;"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" stroke-width="2"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2"/><path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2"/></svg>
                        {{ count($cart) }} Item
                    </h2>
                    <a href="{{ route('cart.clear') }}" class="cart-clear"
                       onclick="return confirm('Kosongkan keranjang?')">Kosongkan</a>
                </div>

                @foreach($cart as $id => $item)
                <div class="cart-item" id="cart-row-{{ $id }}">
                    {{-- Image --}}
                    <div class="cart-item-img">
                        @if(!empty($item['image']))
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                        @else
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.5"/><path d="m21 15-5-5L5 21" stroke="currentColor" stroke-width="1.5"/></svg>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="cart-item-info">
                        <div class="cart-item-name">{{ $item['name'] }}</div>
                        <div class="cart-item-price">Rp {{ number_format($item['price'], 0, ',', '.') }} / unit</div>
                    </div>

                    {{-- Subtotal --}}
                    <div class="cart-item-subtotal" id="subtotal-{{ $id }}">
                        Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                    </div>

                    {{-- Qty Control --}}
                    <div class="cart-qty">
                        <button class="qty-btn" type="button" onclick="updateQty({{ $id }}, -1)">−</button>
                        <input type="number" class="qty-input" id="qty-{{ $id }}"
                               value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock'] }}"
                               onchange="setQty({{ $id }}, this.value)">
                        <button class="qty-btn" type="button" onclick="updateQty({{ $id }}, 1)">+</button>
                    </div>

                    {{-- Remove --}}
                    <button class="cart-remove" title="Hapus" onclick="removeItem({{ $id }})">
                        <svg viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Order Summary --}}
        <div>
            <div class="order-summary">
                <div class="order-summary-header">Ringkasan Pesanan</div>
                <div class="order-summary-body">
                    @foreach($cart as $id => $item)
                    <div class="summary-item">
                        <span class="label" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $item['name'] }}</span>
                        <span class="value">×{{ $item['quantity'] }}</span>
                    </div>
                    @endforeach

                    <hr class="summary-divider">

                    <div class="summary-total">
                        <span class="label">Total</span>
                        <span class="value" id="grand-total">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="order-summary-footer">
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg btn-full" id="btn-checkout">
                        <svg viewBox="0 0 24 24" fill="none" width="20" height="20"><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="21" r="1" stroke="currentColor" stroke-width="2"/><circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2"/></svg>
                        Lanjutkan Checkout
                    </a>
                    <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-full" style="margin-top:10px;">
                        ← Lanjut Belanja
                    </a>
                    <div class="alert alert-info" style="margin-top:16px; font-size:12px;">
                        🔒 Pembayaran diproses aman melalui <strong>DOKU</strong> Payment Gateway
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
const cartToken = document.querySelector('meta[name="csrf-token"]').content;

async function updateQty(productId, delta) {
    const input = document.getElementById('qty-' + productId);
    const newQty = Math.max(1, parseInt(input.value) + delta);
    input.value = newQty;
    await setQty(productId, newQty);
}

async function setQty(productId, qty) {
    qty = Math.max(1, parseInt(qty));
    const res = await fetch('{{ route("cart.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cartToken },
        body: JSON.stringify({ product_id: productId, quantity: qty })
    });
    const data = await res.json();
    if (data.success) {
        // Update subtotal
        const sub = document.getElementById('subtotal-' + productId);
        if (sub) sub.textContent = 'Rp ' + formatNum(data.subtotal);
        // Update cart badge
        const badge = document.getElementById('cart-badge');
        if (badge) { badge.textContent = data.count; badge.classList.toggle('hidden', data.count === 0); }
    }
}

async function removeItem(productId) {
    if (!confirm('Hapus item dari keranjang?')) return;
    const res = await fetch('{{ route("cart.remove") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cartToken },
        body: JSON.stringify({ product_id: productId })
    });
    const data = await res.json();
    if (data.success) {
        document.getElementById('cart-row-' + productId)?.remove();
        const badge = document.getElementById('cart-badge');
        if (badge) { badge.textContent = data.count; badge.classList.toggle('hidden', data.count === 0); }
        if (data.count === 0) location.reload();
    }
}

function formatNum(n) {
    return parseInt(n).toLocaleString('id-ID');
}
</script>
@endpush
