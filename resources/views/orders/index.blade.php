@extends('layouts.app')
@section('title', 'Histori Belanja — MitraSpace')

@section('content')
<div class="container" style="padding: 40px 0; max-width: 900px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 700; color: var(--color-text); margin: 0;">Histori Belanja</h1>
            <p style="color: var(--color-text-3); font-size: 14px; margin-top: 4px;">Pantau status pembayaran dan lacak semua pesanan Anda di sini.</p>
        </div>
        <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-sm">Lanjut Belanja</a>
    </div>

    @if($orders->isEmpty())
        <div style="background: var(--color-surface); border-radius: var(--radius-lg); padding: 60px 20px; text-align: center; border: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
            <div style="width: 80px; height: 80px; background: rgba(108, 99, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                <svg viewBox="0 0 24 24" fill="none" style="width: 40px; height: 40px; color: var(--color-primary); stroke: currentColor; stroke-width: 2;">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="3" y1="6" x2="21" y2="6" stroke-linecap="round"/>
                    <path d="M16 10a4 4 0 0 1-8 0" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 600; color: var(--color-text); margin-bottom: 8px;">Belum Ada Transaksi</h3>
            <p style="color: var(--color-text-3); max-width: 400px; margin: 0 auto 24px; font-size: 14px; line-height: 1.6;">Anda belum memiliki riwayat pembelian di MitraSpace. Cari produk menarik kami dan mulailah berbelanja!</p>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary">Mulai Belanja</a>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 24px;">
            @foreach($orders as $order)
                <div style="background: var(--color-surface); border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;">
                    <!-- Card Header -->
                    <div style="padding: 16px 24px; background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--color-border); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                            <div>
                                @if(!empty($order['seller_order_number']))
                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-3); display: block;">Nomor Order (Seller)</span>
                                    <span style="font-family: monospace; font-weight: 700; font-size: 14px; color: var(--color-primary); display: block; margin-bottom: 2px;">{{ $order['seller_order_number'] }}</span>
                                    <span style="font-size: 11px; color: var(--color-text-3); display: block; font-family: monospace;">Invoice: {{ $order['invoice_number'] }}</span>
                                @else
                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-3); display: block;">Nomor Invoice</span>
                                    <span style="font-family: monospace; font-weight: 600; font-size: 14px; color: var(--color-text); display: block;">{{ $order['invoice_number'] }}</span>
                                @endif
                            </div>
                            <div style="width: 1px; height: 24px; background: var(--color-border); display: none; @media(min-width: 576px){display: block;}"></div>
                            <div>
                                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-3); display: block;">Tanggal Transaksi</span>
                                <span style="font-size: 13px; color: var(--color-text-2);">{{ $order['created_at']->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        @php
                            $status = strtoupper($order['status']);
                            $badgeClass = '';
                            $statusText = '';
                            
                            if (in_array($status, ['SUCCESS', 'PAID', 'SETTLEMENT'])) {
                                $badgeClass = 'background: rgba(16, 185, 129, 0.1); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.2);';
                                $statusText = 'Berhasil';
                            } elseif (in_array($status, ['FORWARDED_TO_SELLER'])) {
                                $badgeClass = 'background: rgba(59, 130, 246, 0.1); color: #3B82F6; border: 1px solid rgba(59, 130, 246, 0.2);';
                                $statusText = 'Diteruskan ke Seller';
                            } elseif (in_array($status, ['PENDING'])) {
                                $badgeClass = 'background: rgba(245, 158, 11, 0.1); color: #F59E0B; border: 1px solid rgba(245, 158, 11, 0.2);';
                                $statusText = 'Menunggu Pembayaran';
                            } elseif (in_array($status, ['FAILED', 'CANCEL', 'EXPIRED'])) {
                                $badgeClass = 'background: rgba(239, 68, 68, 0.1); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.2);';
                                $statusText = 'Gagal';
                            } else {
                                $badgeClass = 'background: rgba(107, 114, 128, 0.1); color: #6B7280; border: 1px solid rgba(107, 114, 128, 0.2);';
                                $statusText = $status;
                            }
                        @endphp
                        <span style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; {{ $badgeClass }}">
                            {{ $statusText }}
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div style="padding: 24px;">
                        <!-- Items list -->
                        <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 20px;">
                            @foreach($order['items'] as $item)
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 42px; height: 42px; background: rgba(108, 99, 255, 0.05); border-radius: var(--radius-sm); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                            📦
                                        </div>
                                        <div>
                                            <span style="font-weight: 600; font-size: 14px; color: var(--color-text); display: block;">{{ $item['name'] }}</span>
                                            <span style="font-size: 12px; color: var(--color-text-3);">{{ $item['quantity'] }} barang × Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <span style="font-weight: 500; font-size: 14px; color: var(--color-text);">Rp {{ number_format($item['subtotal'] ?? ($item['price'] * $item['quantity']), 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <hr style="border: 0; border-top: 1px solid var(--color-border); margin: 0 0 20px 0;">

                        <!-- Footer Details -->
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px;">
                            <div>
                                @if(!empty($order['courier']))
                                    <div style="font-size: 13px; color: var(--color-text-2); margin-bottom: 4px;">
                                        🚚 <strong>Pengiriman:</strong> {{ strtoupper($order['courier']) }} ({{ $order['courier_service'] }}) ke {{ $order['destination_city'] }}
                                    </div>
                                @endif
                                <div style="font-size: 12px; color: var(--color-text-3);">
                                    Total Ongkos Kirim: Rp {{ number_format($order['shipping_cost'], 0, ',', '.') }}
                                </div>
                            </div>

                            <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 12px;">
                                <div>
                                    <span style="font-size: 13px; color: var(--color-text-2); margin-right: 8px;">Total Belanja:</span>
                                    <span style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: var(--color-primary);">Rp {{ number_format($order['amount'], 0, ',', '.') }}</span>
                                </div>

                                @if(in_array($status, ['FORWARDED_TO_SELLER', 'SUCCESS', 'PAID', 'SETTLEMENT']))
                                    <a href="{{ route('tracking.show', $order['seller_order_number'] ?: $order['invoice_number']) }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                        <svg viewBox="0 0 24 24" fill="none" width="16" height="16" style="stroke: currentColor; stroke-width: 2.5;"><path d="M9 20H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4"/><path d="M16 3v4M8 3v4M3 11h18"/><path d="m16 19 2 2 4-4"/></svg>
                                        Lacak Pesanan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
