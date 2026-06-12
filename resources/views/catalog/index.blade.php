@extends('layouts.app')

@section('title', 'Katalog Produk — Belanja Terbaik')
@section('meta_description', 'Temukan ribuan produk pilihan terbaik di MitraSpace. Harga terjangkau, pengiriman cepat, dan belanja mudah.')

@section('content')

{{-- HERO --}}
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">🛍️ Platform Belanja Terpercaya</div>
            <h1>Belanja Produk <span class="gradient-text">Pilihan Terbaik</span> Dengan Harga Terjangkau</h1>
            <p class="hero-desc">
                Ribuan produk dari seller terpercaya, proses pembayaran aman via DOKU,
                dan pengiriman ke seluruh Indonesia dengan berbagai pilihan kurir.
            </p>
            <div class="hero-actions">
                <a href="#catalog" class="btn btn-primary btn-lg">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2"/></svg>
                    Jelajahi Produk
                </a>
                <a href="{{ route('tracking.index') }}" class="btn btn-outline btn-lg">Lacak Pesanan</a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-num">10K+</div>
                    <div class="stat-label">Produk Tersedia</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">5K+</div>
                    <div class="stat-label">Pelanggan Puas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">99%</div>
                    <div class="stat-label">Pesanan Berhasil</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CATALOG --}}
<section class="section" id="catalog">
    <div class="container">

        {{-- Section Header --}}
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    @if($search)
                        Hasil Pencarian "<span>{{ $search }}</span>"
                    @elseif($category)
                        Kategori: <span>{{ $category }}</span>
                    @else
                        Semua <span>Produk</span>
                    @endif
                </h2>
                <p class="section-subtitle">{{ count($products) }} produk ditemukan</p>
            </div>
            @if($search || $category)
            <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
            @endif
        </div>

        {{-- Category Filter --}}
        @if(!empty($categories))
        <div class="category-filter">
            <a href="{{ route('catalog.index') }}" class="cat-btn {{ !$category ? 'active' : '' }}">Semua</a>
            @foreach($categories as $cat)
            <a href="{{ route('catalog.index', ['category' => $cat]) }}"
               class="cat-btn {{ $category === $cat ? 'active' : '' }}">{{ $cat }}</a>
            @endforeach
        </div>
        @endif

        {{-- Product Grid --}}
        @if(empty($products))
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.5"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <h3>Produk Tidak Ditemukan</h3>
            <p>Coba kata kunci lain atau reset filter kategori.</p>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary">Lihat Semua Produk</a>
        </div>
        @else
        <div class="product-grid" id="product-grid">
            @foreach($products as $product)
            @php
                $stock = (int)($product['stock'] ?? 0);
                $price = number_format((float)($product['price'] ?? 0), 0, ',', '.');
            @endphp
            <div class="product-card" id="product-{{ $product['id'] }}">
                <div class="product-img-wrap">
                    @if(!empty($product['image_url']))
                        <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    @else
                        <div class="product-img-placeholder">
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.5"/><path d="m21 15-5-5L5 21" stroke="currentColor" stroke-width="1.5"/></svg>
                            <span>{{ $product['category'] ?? 'Produk' }}</span>
                        </div>
                    @endif

                    @if($stock <= 0)
                    <span class="product-badge badge-out">Habis</span>
                    @elseif($stock <= 5)
                    <span class="product-badge" style="background:#FFD166;color:#333">Sisa {{ $stock }}</span>
                    @endif

                    <button class="product-wishlist" title="Simpan ke wishlist">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                </div>

                <div class="product-body">
                    @if(!empty($product['category']))
                    <div class="product-category">{{ $product['category'] }}</div>
                    @endif
                    <h3 class="product-name">{{ $product['name'] }}</h3>
                    <div class="product-price">Rp {{ $price }}</div>
                    <div class="product-stock {{ $stock <= 0 ? 'out' : ($stock <= 5 ? 'low' : '') }}">
                        @if($stock <= 0)
                            Stok Habis
                        @elseif($stock <= 5)
                            ⚠️ Sisa {{ $stock }} unit
                        @else
                            Stok: {{ $stock }} unit
                        @endif
                    </div>
                </div>

                <div class="product-footer">
                    <div style="display:flex; gap:8px;">
                        <a href="{{ route('catalog.show', $product['id']) }}" class="btn btn-outline btn-sm" style="flex:1">Detail</a>
                        @if($stock > 0)
                        <form action="{{ route('cart.add') }}" method="POST" style="flex:1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <button type="submit" class="btn btn-primary btn-sm btn-full" id="add-cart-{{ $product['id'] }}">
                                + Keranjang
                            </button>
                        </form>
                        @else
                        <button class="btn btn-sm btn-full" disabled style="background:#F3F4F6;color:#9CA3AF;flex:1">Habis</button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</section>

@endsection

@push('scripts')
<script>
// Wishlist toggle (UI only)
document.querySelectorAll('.product-wishlist').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.classList.toggle('active');
        const svg = btn.querySelector('svg path');
        if (btn.classList.contains('active')) {
            svg.setAttribute('fill', '#FF6B6B');
            svg.setAttribute('stroke', '#FF6B6B');
        } else {
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
        }
    });
});

// Smooth scroll to catalog
document.querySelector('a[href="#catalog"]')?.addEventListener('click', e => {
    e.preventDefault();
    document.getElementById('catalog')?.scrollIntoView({ behavior: 'smooth' });
});
</script>
@endpush
