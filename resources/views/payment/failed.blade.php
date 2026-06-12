@extends('layouts.app')
@section('title', 'Pembayaran Gagal — MitraSpace')
@section('content')

<div class="result-page">
    <div class="result-card">
        <div class="result-icon failed">
            <svg viewBox="0 0 24 24" fill="none" style="color:#DC2626;">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.5"/>
                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </div>

        <h2 style="color:#DC2626;">Pembayaran Gagal</h2>
        <p>Maaf, pembayaran Anda tidak berhasil diproses.<br>
        Tidak ada dana yang dibebankan ke akun Anda.</p>

        @if($reason)
        <div class="alert alert-danger" style="margin-bottom:24px; font-size:13px; text-align:left;">
            ⚠️ <strong>Alasan:</strong> {{ $reason }}
        </div>
        @endif

        <div class="alert alert-info" style="margin-bottom:24px; font-size:13px; text-align:left;">
            💡 <strong>Yang bisa Anda lakukan:</strong>
            <ul style="margin-top:8px; padding-left:16px; line-height:1.8;">
                <li>Pastikan saldo/limit kartu mencukupi</li>
                <li>Coba metode pembayaran lain</li>
                <li>Hubungi bank Anda jika masalah berlanjut</li>
            </ul>
        </div>

        <div style="display:flex; flex-direction:column; gap:10px;">
            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><polyline points="1 4 1 10 7 10" stroke="currentColor" stroke-width="2"/><path d="M3.51 15a9 9 0 1 0 .49-4" stroke="currentColor" stroke-width="2"/></svg>
                Coba Lagi
            </a>
            <a href="{{ route('cart.index') }}" class="btn btn-outline btn-lg">Kembali ke Keranjang</a>
            <a href="{{ route('catalog.index') }}" class="btn btn-sm" style="color:var(--color-text-3);">Lanjut Belanja</a>
        </div>
    </div>
</div>

@endsection
