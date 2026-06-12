@extends('layouts.app')

@section('title', ($product['name'] ?? 'Produk') . ' — MitraSpace')
@section('meta_description', $product['description'] ?? 'Detail produk di MitraSpace E-commerce.')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('catalog.index') }}">Katalog</a>
            <span>›</span>
            @if(!empty($product['category']))
            <a href="{{ route('catalog.index', ['category' => $product['category']]) }}">{{ $product['category'] }}</a>
            <span>›</span>
            @endif
            <span>{{ $product['name'] }}</span>
        </div>
    </div>
</div>

{{-- Product Detail --}}
<div class="container">
    <div class="product-detail-layout">

        {{-- Image --}}
        <div>
            <div class="product-detail-img">
                @if(!empty($product['image_url']))
                    <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <div style="text-align:center; color: var(--color-primary); opacity:.5;">
                        <svg viewBox="0 0 24 24" fill="none" width="120" height="120"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.2"/><circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.2"/><path d="m21 15-5-5L5 21" stroke="currentColor" stroke-width="1.2"/></svg>
                        <p style="font-size:13px; margin-top:10px; font-weight:600;">Gambar tidak tersedia</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info --}}
        <div class="product-detail-info">
            @if(!empty($product['category']))
            <div class="product-category" style="font-size:13px; font-weight:700; color:var(--color-secondary); text-transform:uppercase; letter-spacing:.8px;">
                {{ $product['category'] }}
            </div>
            @endif

            <h1 style="font-size:1.9rem; font-weight:900; line-height:1.2;">{{ $product['name'] }}</h1>

            <div class="detail-price">Rp {{ number_format((float)($product['price'] ?? 0), 0, ',', '.') }}</div>

            @if(!empty($product['description']))
            <p class="detail-desc">{{ $product['description'] }}</p>
            @endif

            {{-- Meta Info --}}
            <div class="detail-meta">
                @php $stock = (int)($product['stock'] ?? 0); @endphp
                <div class="meta-row">
                    <span class="meta-label">Stok</span>
                    <span class="meta-value {{ $stock <= 0 ? 'text-danger' : ($stock <= 5 ? 'text-warning' : '') }}"
                          style="{{ $stock <= 0 ? 'color:var(--color-danger);font-weight:700' : ($stock <= 5 ? 'color:var(--color-warning);font-weight:700' : '') }}">
                        @if($stock <= 0) Habis @else {{ $stock }} unit tersedia @endif
                    </span>
                </div>
                @if(!empty($product['weight']))
                <div class="meta-row">
                    <span class="meta-label">Berat</span>
                    <span class="meta-value">{{ $product['weight'] }} kg</span>
                </div>
                @endif
            </div>

            {{-- Add to Cart --}}
            @if($stock > 0)
            <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                <div class="qty-selector">
                    <label style="font-size:13px; font-weight:600; color:var(--color-text-2); text-transform:uppercase; letter-spacing:.5px;">Jumlah:</label>
                    <div class="cart-qty">
                        <button type="button" class="qty-btn" id="qty-minus">−</button>
                        <input type="number" class="qty-input" name="quantity" id="qty-input" value="1" min="1" max="{{ $stock }}">
                        <button type="button" class="qty-btn" id="qty-plus">+</button>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex:2;" id="btn-add-cart">
                        <svg viewBox="0 0 24 24" fill="none" width="20" height="20"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" stroke-width="2"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2"/><path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2"/></svg>
                        Tambah ke Keranjang
                    </button>
                    <a href="{{ route('cart.index') }}" class="btn btn-outline btn-lg" style="flex:1;">Lihat Keranjang</a>
                </div>
            </form>
            @else
            <div class="alert alert-danger" style="margin-top:1rem;">
                <strong>Stok Habis</strong> — Produk ini sedang tidak tersedia.
            </div>
            @endif

            {{-- Shipping Note --}}
            <div style="background:var(--color-bg); border-radius:var(--radius-md); padding:14px 16px; border:1px solid var(--color-border); display:flex; align-items:flex-start; gap:10px;">
                <svg viewBox="0 0 24 24" fill="none" width="20" height="20" style="color:var(--color-secondary);flex-shrink:0;margin-top:2px;"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3" stroke="currentColor" stroke-width="2"/><rect x="9" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="21" r="1" stroke="currentColor" stroke-width="2"/><circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2"/></svg>
                <div>
                    <p style="font-size:13px; font-weight:600; color:var(--color-text);">Estimasi Pengiriman</p>
                    <p style="font-size:12px; color:var(--color-text-3);">Dikirim dengan JNE, J&T, SiCepat, dan Anteraja. Cek ongkir saat checkout.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if(!empty($related))
    <div class="section" style="border-top:1px solid var(--color-border); padding-top: var(--space-2xl);">
        <div class="section-header">
            <div>
                <h2 class="section-title">Produk <span>Serupa</span></h2>
                <p class="section-subtitle">Kategori {{ $product['category'] ?? '' }}</p>
            </div>
        </div>
        <div class="product-grid">
            @foreach($related as $rel)
            @php $relStock = (int)($rel['stock'] ?? 0); @endphp
            <div class="product-card">
                <div class="product-img-wrap">
                    @if(!empty($rel['image_url']))
                        <img src="{{ $rel['image_url'] }}" alt="{{ $rel['name'] }}" loading="lazy">
                    @else
                        <div class="product-img-placeholder">
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/></svg>
                            <span>{{ $rel['category'] ?? '' }}</span>
                        </div>
                    @endif
                </div>
                <div class="product-body">
                    <div class="product-category">{{ $rel['category'] ?? '' }}</div>
                    <h3 class="product-name">{{ $rel['name'] }}</h3>
                    <div class="product-price">Rp {{ number_format((float)($rel['price'] ?? 0), 0, ',', '.') }}</div>
                </div>
                <div class="product-footer">
                    <a href="{{ route('catalog.show', $rel['id']) }}" class="btn btn-outline btn-sm btn-full">Lihat Detail</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
const qtyInput = document.getElementById('qty-input');
const maxStock = {{ (int)($product['stock'] ?? 99) }};

document.getElementById('qty-minus')?.addEventListener('click', () => {
    const v = parseInt(qtyInput.value);
    if (v > 1) qtyInput.value = v - 1;
});
document.getElementById('qty-plus')?.addEventListener('click', () => {
    const v = parseInt(qtyInput.value);
    if (v < maxStock) qtyInput.value = v + 1;
});
</script>
@endpush
