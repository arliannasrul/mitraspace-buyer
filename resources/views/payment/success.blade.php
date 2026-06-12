@extends('layouts.app')
@section('title', 'Pembayaran Berhasil — MitraSpace')
@section('content')

<div class="result-page">
    <div class="result-card">
        <div class="result-icon success">
            <svg viewBox="0 0 24 24" fill="none" style="color:#059669;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                <polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h2 style="color:#059669;">Pembayaran Berhasil! 🎉</h2>
        <p>Terima kasih telah berbelanja di <strong>MitraSpace</strong>.<br>
        Pesanan Anda sedang diproses dan akan segera dikirim.</p>

        @if($order)
        <div style="background:var(--color-bg); border-radius:var(--radius-md); padding:16px; margin-bottom:24px; text-align:left;">
            <div class="summary-item" style="padding:6px 0; font-size:14px;">
                <span class="label">No. Invoice</span>
                <span class="value" style="font-family:monospace; font-size:13px;">{{ $order['invoice_number'] ?? '-' }}</span>
            </div>
            <div class="summary-item" style="padding:6px 0; font-size:14px;">
                <span class="label">Total Dibayar</span>
                <span class="value" style="color:var(--color-primary);">Rp {{ number_format($order['amount'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item" style="padding:6px 0; font-size:14px;">
                <span class="label">Ekspedisi</span>
                <span class="value">{{ $order['courier'] ?? '-' }} — {{ $order['courier_service'] ?? '-' }}</span>
            </div>
            <div class="summary-item" style="padding:6px 0; font-size:14px;">
                <span class="label">Dikirim ke</span>
                <span class="value">{{ $order['shipping_address']['city'] ?? '-' }}</span>
            </div>
        </div>
        @endif

        <div class="alert alert-info" style="margin-bottom:24px; font-size:13px; text-align:left;">
            📦 Seller kami sedang memproses pesanan Anda. Nomor resi akan tersedia setelah barang dikemas.
        </div>

        <div style="display:flex; flex-direction:column; gap:10px;">
            @if($order && !empty($order['invoice_number']))
            <a href="{{ route('tracking.show', $order['invoice_number']) }}" class="btn btn-primary btn-lg">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M9 20H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4" stroke="currentColor" stroke-width="2"/><path d="M16 3v4M8 3v4M3 11h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="m16 19 2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Lacak Pesanan
            </a>
            @endif
            <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-lg">Lanjut Belanja</a>
        </div>
    </div>
</div>

@endsection
