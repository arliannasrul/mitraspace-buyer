@extends('layouts.app')
@section('title', 'Status Pesanan ' . $orderNumber . ' — MitraSpace')
@section('content')

<div class="page-header">
    <div class="container">
        <h1>Status Pesanan</h1>
        <div class="breadcrumb">
            <a href="{{ route('catalog.index') }}">Beranda</a>
            <span>›</span>
            <a href="{{ route('tracking.index') }}">Lacak Pesanan</a>
            <span>›</span>
            <span>{{ $orderNumber }}</span>
        </div>
    </div>
</div>

<div class="container" style="padding-top:2rem; padding-bottom:3rem;">
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.5rem;">

        {{-- Main Tracking --}}
        <div>

            {{-- Status Card --}}
            <div class="tracking-card">
                <div class="tracking-status-row">
                    <div>
                        <p style="font-size:12px; color:var(--color-text-3); font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Nomor Pesanan</p>
                        <p style="font-size:1.1rem; font-weight:800; font-family:monospace; color:var(--color-text);">{{ $orderNumber }}</p>
                    </div>
                    @php
                        $status = strtolower($tracking['status'] ?? 'unknown');
                        $statusClass = match($status) {
                            'baru', 'new', 'pending', 'paid' => 'status-new',
                            'dikirim', 'shipped', 'in_transit', 'on_delivery' => 'status-shipped',
                            'selesai', 'delivered', 'success', 'completed' => 'status-delivered',
                            'dibatalkan', 'cancelled', 'failed' => 'status-cancelled',
                            default => 'status-new'
                        };
                        $statusLabel = match($status) {
                            'baru', 'new', 'pending' => 'Pesanan Baru',
                            'paid' => 'Dibayar',
                            'dikirim', 'shipped', 'in_transit' => 'Dalam Pengiriman',
                            'on_delivery' => 'Dalam Pengiriman',
                            'selesai', 'delivered', 'completed' => 'Terkirim ✓',
                            'success' => 'Sukses ✓',
                            'dibatalkan', 'cancelled' => 'Dibatalkan',
                            'failed' => 'Gagal',
                            default => ucfirst($status)
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                {{-- Order Info --}}
                @if(!empty($tracking['order_number']) || !empty($tracking['courier']) || !empty($tracking['awb']))
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; padding:1rem; background:var(--color-bg); border-radius:var(--radius-md); margin-bottom:1.5rem;">
                    @if(!empty($tracking['courier']))
                    <div>
                        <p style="font-size:11px; color:var(--color-text-3); font-weight:600; text-transform:uppercase;">Ekspedisi</p>
                        <p style="font-size:14px; font-weight:700; margin-top:4px;">{{ $tracking['courier'] }}</p>
                    </div>
                    @endif
                    @if(!empty($tracking['awb']) || !empty($tracking['tracking_number']))
                    <div>
                        <p style="font-size:11px; color:var(--color-text-3); font-weight:600; text-transform:uppercase;">No. Resi</p>
                        <p style="font-size:14px; font-weight:700; margin-top:4px; font-family:monospace;">{{ $tracking['awb'] ?? $tracking['tracking_number'] ?? '-' }}</p>
                    </div>
                    @endif
                    @if(!empty($tracking['estimated_delivery']))
                    <div>
                        <p style="font-size:11px; color:var(--color-text-3); font-weight:600; text-transform:uppercase;">Est. Tiba</p>
                        <p style="font-size:14px; font-weight:700; margin-top:4px; color:var(--color-success);">{{ $tracking['estimated_delivery'] }}</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Timeline --}}
                <h3 style="font-size:15px; font-weight:700; margin-bottom:1.5rem; color:var(--color-text);">
                    <svg viewBox="0 0 24 24" fill="none" width="18" height="18" style="display:inline;vertical-align:middle;margin-right:6px;color:var(--color-primary);"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="currentColor" stroke-width="2"/></svg>
                    Riwayat Pergerakan Paket
                </h3>

                @php
                    $history = $tracking['history'] ?? $tracking['timeline'] ?? $tracking['events'] ?? [];
                    // Buat fallback history berdasarkan status jika kosong
                    if (empty($history)) {
                        $history = [['event' => $statusLabel, 'description' => 'Status terkini pesanan Anda', 'timestamp' => $tracking['updated_at'] ?? now()->toIso8601String()]];
                    }
                @endphp

                @if(empty($history))
                <div class="empty-state" style="padding:40px 0;">
                    <p style="color:var(--color-text-3); font-size:14px;">Belum ada riwayat pergerakan paket.</p>
                </div>
                @else
                <div class="tracking-timeline">
                    @foreach($history as $i => $event)
                    <div class="timeline-item {{ $i === 0 ? 'active' : '' }}">
                        <div class="timeline-indicator">
                            <div class="timeline-dot"></div>
                            @if(!$loop->last)
                            <div class="timeline-line"></div>
                            @endif
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-event">{{ $event['event'] ?? $event['status'] ?? 'Update' }}</div>
                            @if(!empty($event['description']) || !empty($event['note']))
                            <div class="timeline-desc">{{ $event['description'] ?? $event['note'] ?? '' }}</div>
                            @endif
                            @if(!empty($event['location']))
                            <div class="timeline-desc">📍 {{ $event['location'] }}</div>
                            @endif
                            <div class="timeline-time">
                                {{ !empty($event['timestamp']) ? \Carbon\Carbon::parse($event['timestamp'])->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : ($event['date'] ?? '') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

        {{-- Sidebar --}}
        <div>

            {{-- Shipping Address --}}
            @if(!empty($tracking['shipping_address']) || !empty($tracking['customer']))
            <div class="tracking-card" style="margin-bottom:1rem;">
                <h3 style="font-size:14px; font-weight:700; margin-bottom:1rem; color:var(--color-text);">📦 Info Pengiriman</h3>
                @php $addr = $tracking['shipping_address'] ?? $tracking['customer'] ?? []; @endphp
                @if(!empty($addr['name']))
                <div style="font-size:14px; line-height:1.8; color:var(--color-text-2);">
                    <strong>{{ $addr['name'] }}</strong><br>
                    @if(!empty($addr['phone'])) {{ $addr['phone'] }}<br>@endif
                    @if(!empty($addr['address'])) {{ $addr['address'] }}<br>@endif
                    @if(!empty($addr['city'])) {{ $addr['city'] }}@endif
                </div>
                @else
                <p style="font-size:13px; color:var(--color-text-3);">Informasi tidak tersedia.</p>
                @endif
            </div>
            @endif

            {{-- Items --}}
            @if(!empty($tracking['items']))
            <div class="tracking-card">
                <h3 style="font-size:14px; font-weight:700; margin-bottom:1rem; color:var(--color-text);">🛍️ Item Pesanan</h3>
                @foreach($tracking['items'] as $item)
                <div style="display:flex; justify-content:space-between; align-items:flex-start; padding:8px 0; border-bottom:1px solid var(--color-border); font-size:13px;">
                    <span style="color:var(--color-text-2); max-width:150px;">{{ $item['name'] ?? '-' }} ×{{ $item['quantity'] ?? 1 }}</span>
                    <span style="font-weight:700; color:var(--color-primary);">Rp {{ number_format($item['subtotal'] ?? ($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Track Another --}}
            <div class="tracking-card" style="margin-top:1rem;">
                <h3 style="font-size:14px; font-weight:700; margin-bottom:1rem;">🔍 Lacak Pesanan Lain</h3>
                <form action="{{ route('tracking.search') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <input type="text" class="form-control" name="order_number"
                               placeholder="Nomor pesanan lain..." required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full btn-sm">Lacak</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
