@extends('layouts.app')
@section('title', 'Lacak Pesanan — MitraSpace')
@section('meta_description', 'Lacak status pengiriman pesanan Anda secara real-time di MitraSpace E-commerce.')
@section('content')

{{-- Hero --}}
<section class="tracking-hero">
    <div class="container">
        <h1>Lacak <span style="color:var(--color-primary);">Pesanan</span></h1>
        <p>Masukkan nomor pesanan Anda untuk melihat status terkini dan riwayat pengiriman paket.</p>

        <form action="{{ route('tracking.search') }}" method="POST" class="tracking-search-box">
            @csrf
            <input type="text" name="order_number" id="order-input"
                   placeholder="Contoh: INV-ABCD1234-20240601120000"
                   value="{{ $searched ?? '' }}" required autocomplete="off">
            <button type="submit" id="btn-track">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18" style="display:inline;vertical-align:middle;margin-right:6px;"><path d="M9 20H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4" stroke="currentColor" stroke-width="2"/><path d="M16 3v4M8 3v4M3 11h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="m16 19 2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Lacak
            </button>
        </form>

        @if(session('error'))
        <div class="alert alert-danger" style="max-width:540px; margin:16px auto 0; text-align:left;">
            {{ session('error') }}
        </div>
        @endif
    </div>
</section>

{{-- Features --}}
<section class="section" style="padding: 60px 0;">
    <div class="container">
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; margin-bottom:3rem;">
            @php
            $features = [
                ['icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/>', 'title' => 'Aman & Terpercaya', 'desc' => 'Status pesanan diambil langsung dari sistem internal seller'],
                ['icon' => '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><polyline points="12 6 12 12 16 14" stroke="currentColor" stroke-width="2"/>', 'title' => 'Real-time Update', 'desc' => 'Informasi terkini tentang pergerakan paket Anda'],
                ['icon' => '<path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3" stroke="currentColor" stroke-width="2"/><rect x="9" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="21" r="1" stroke="currentColor" stroke-width="2"/><circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2"/>', 'title' => 'Multi Ekspedisi', 'desc' => 'JNE, J&T, SiCepat, Anteraja, dan lebih banyak lagi'],
            ];
            @endphp
            @foreach($features as $f)
            <div style="background:var(--color-surface); border-radius:var(--radius-lg); border:1px solid var(--color-border); padding:1.5rem; text-align:center;">
                <div style="width:56px; height:56px; background:var(--color-primary-light); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" width="26" height="26" style="color:var(--color-primary);">{!! $f['icon'] !!}</svg>
                </div>
                <h3 style="font-size:15px; font-weight:700; margin-bottom:6px;">{{ $f['title'] }}</h3>
                <p style="font-size:13px; color:var(--color-text-3);">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
