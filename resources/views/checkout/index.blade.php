@extends('layouts.app')

@section('title', 'Checkout — MitraSpace')

@section('content')

<div class="page-header">
    <div class="container">
        <h1>Checkout</h1>
        <div class="breadcrumb">
            <a href="{{ route('catalog.index') }}">Katalog</a>
            <span>›</span>
            <a href="{{ route('cart.index') }}">Keranjang</a>
            <span>›</span>
            <span>Checkout</span>
        </div>
    </div>
</div>

<div class="container">
    <form action="{{ route('checkout.pay') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="shipping_cost" id="shipping-cost-input" value="0">
        <input type="hidden" name="courier" id="courier-input" value="">
        <input type="hidden" name="courier_service" id="courier-service-input" value="">

        <div class="checkout-layout">
            {{-- LEFT: Form --}}
            <div>
                @auth
                {{-- 1. Alamat Pengiriman --}}
                <div class="checkout-section">
                    <div class="checkout-section-header">
                        <div class="step-num">1</div>
                        <h3>Alamat Pengiriman</h3>
                    </div>
                    <div class="checkout-section-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                            <div class="form-group">
                                <label class="form-label" for="recipient_name">Nama Penerima *</label>
                                <input type="text" class="form-control {{ $errors->has('recipient_name') ? 'is-invalid' : '' }}"
                                       id="recipient_name" name="recipient_name"
                                       value="{{ old('recipient_name', auth()->user()->name) }}"
                                       placeholder="Nama lengkap penerima" required>
                                @error('recipient_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="recipient_phone">No. Handphone *</label>
                                <input type="tel" class="form-control {{ $errors->has('recipient_phone') ? 'is-invalid' : '' }}"
                                       id="recipient_phone" name="recipient_phone"
                                       value="{{ old('recipient_phone') }}"
                                       placeholder="08xxxxxxxxxx" required>
                                @error('recipient_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="customer_email">Email (Opsional)</label>
                            <input type="email" class="form-control"
                                   id="customer_email" name="customer_email"
                                   value="{{ old('customer_email', auth()->user()->email) }}"
                                   placeholder="email@contoh.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="address">Alamat Lengkap *</label>
                            <textarea class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                      id="address" name="address" rows="3"
                                      placeholder="Nama jalan, nomor, RT/RW, kelurahan, kecamatan..." required>{{ old('address') }}</textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="city">Kota Tujuan *</label>
                            <select class="form-control {{ $errors->has('city') ? 'is-invalid' : '' }}"
                                    id="city" name="city" required>
                                <option value="">— Pilih Kota —</option>
                                @foreach($cities as $key => $label)
                                <option value="{{ $key }}" {{ old('city') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- 2. Pilih Ekspedisi --}}
                <div class="checkout-section">
                    <div class="checkout-section-header">
                        <div class="step-num">2</div>
                        <h3>Pilih Ekspedisi</h3>
                    </div>
                    <div class="checkout-section-body">
                        <button type="button" class="btn btn-secondary btn-full" id="btn-check-ongkir" onclick="checkOngkir()" style="margin-bottom:1rem;">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3" stroke="currentColor" stroke-width="2"/><rect x="9" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="21" r="1" stroke="currentColor" stroke-width="2"/><circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2"/></svg>
                            Cek Ongkir
                        </button>

                        <div id="shipping-rates-container">
                            <div style="text-align:center; padding:20px; color:var(--color-text-3); font-size:14px;">
                                <svg viewBox="0 0 24 24" fill="none" width="36" height="36" style="margin:0 auto 10px;opacity:.4;"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3" stroke="currentColor" stroke-width="1.5"/><rect x="9" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="21" r="1" stroke="currentColor" stroke-width="1.5"/><circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="1.5"/></svg>
                                Pilih kota tujuan dan klik "Cek Ongkir" untuk melihat pilihan ekspedisi
                            </div>
                        </div>
                    </div>
                </div>
                @else
                {{-- Banner Login untuk Guest --}}
                <div class="checkout-login-alert" style="padding: 2.5rem; background: var(--color-surface); border-radius: 16px; text-align: center; border: 1px solid var(--color-border); box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 2rem;">
                    <div style="background: rgba(108, 99, 255, 0.1); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <svg viewBox="0 0 24 24" fill="none" width="32" height="32" style="color: #6C63FF;"><path d="M12 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10z" fill="currentColor"/><path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <h3 style="margin-bottom: 0.75rem; font-family: 'Outfit', sans-serif; font-size: 1.25rem;">Silakan Login Terlebih Dahulu</h3>
                    <p style="color: var(--color-text-2); margin-bottom: 2rem; font-size: 14px; line-height: 1.6;">
                        Untuk melanjutkan proses pengisian alamat pengiriman dan memproses transaksi pembayaran, Anda wajib masuk menggunakan akun Google Anda.
                    </p>
                    <a href="{{ route('login') }}" class="btn-login-google" style="display: inline-flex; justify-content: center; align-items: center; width: auto; font-size: 15px; padding: 12px 28px; text-decoration: none; border-radius: 8px;">
                        <svg viewBox="0 0 24 24" class="google-icon" width="18" height="18" style="margin-right: 10px;">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                        </svg>
                        <strong>Login dengan Google</strong>
                    </a>
                </div>
                @endauth
            </div>

            {{-- RIGHT: Order Summary --}}
            <div>
                <div class="order-summary">
                    <div class="order-summary-header">Ringkasan Pesanan</div>
                    <div class="order-summary-body">
                        @foreach($cart as $item)
                        <div class="summary-item">
                            <span class="label" style="font-size:13px;">{{ $item['name'] }} ×{{ $item['quantity'] }}</span>
                            <span class="value" style="font-size:13px;">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </div>
                        @endforeach

                        <hr class="summary-divider">

                        <div class="summary-item">
                            <span class="label">Subtotal Produk</span>
                            <span class="value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Total Berat</span>
                            <span class="value">{{ $totalWeight }} kg</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Ongkos Kirim</span>
                            <span class="value" id="display-ongkir" style="color:var(--color-text-3);">Belum dipilih</span>
                        </div>

                        {{-- Voucher Code Section --}}
                        <hr class="summary-divider" style="margin: 0.75rem 0;">
                        <div class="voucher-section" style="padding: 2px 0;">
                            <div class="form-group" style="margin-bottom: 8px;">
                                <label class="form-label" style="font-size: 12px; margin-bottom: 6px; font-weight:600; display:block;">Punya Voucher Diskon?</label>
                                <div style="display: flex; gap: 8px;" id="voucher-input-container">
                                    <input type="text" class="form-control" id="voucher-code" placeholder="Masukkan kode voucher" style="font-size: 13px; height: 38px; padding: 6px 12px; text-transform: uppercase;">
                                    <button type="button" class="btn btn-secondary" id="btn-apply-voucher" onclick="applyVoucher()" style="font-size: 13px; height: 38px; padding: 0 16px; font-weight:600;">Gunakan</button>
                                </div>
                                <div id="voucher-active-container" style="display:none; align-items:center; justify-content:space-between; background: rgba(108, 99, 255, 0.08); padding: 8px 12px; border-radius: 8px; border: 1px dashed var(--color-primary);">
                                    <span style="font-size:13px; font-weight:bold; color:var(--color-primary);" id="active-voucher-label">CODE</span>
                                    <button type="button" onclick="removeVoucher()" style="background:none; border:none; color:#dc3545; font-size:12px; font-weight:bold; cursor:pointer; padding:0 4px;">Hapus</button>
                                </div>
                                <div id="voucher-message" style="font-size: 12px; margin-top: 6px; font-weight: 500; display:none;"></div>
                            </div>
                        </div>

                        <div class="summary-item" id="row-discount" style="display:none; color: var(--color-primary); font-weight: 600;">
                            <span class="label">Diskon Voucher</span>
                            <span class="value" id="display-discount">-Rp 0</span>
                        </div>

                        <div class="summary-total">
                            <span class="label">Total Bayar</span>
                            <span class="value" id="display-total">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="order-summary-footer">
                        @error('courier')
                        <div class="alert alert-danger" style="margin-bottom:12px;font-size:13px;">Pilih ekspedisi terlebih dahulu.</div>
                        @enderror

                        <button type="submit" class="btn btn-primary btn-lg btn-full" id="btn-pay" disabled>
                            <svg viewBox="0 0 24 24" fill="none" width="20" height="20"><rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2"/><line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="2"/></svg>
                            Bayar dengan DOKU
                        </button>
                        <p style="font-size:11px; color:var(--color-text-3); text-align:center; margin-top:10px; line-height:1.5;">
                            🔒 Anda akan diarahkan ke halaman pembayaran DOKU yang aman
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
const subtotal   = {{ (int)$subtotal }};
const totalWeight = {{ (float)$totalWeight }};
const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
let selectedRate = null;
let discountAmount = 0;

async function checkOngkir() {
    const city = document.getElementById('city').value;
    if (!city) {
        alert('Pilih kota tujuan terlebih dahulu!');
        document.getElementById('city').focus();
        return;
    }

    const btn = document.getElementById('btn-check-ongkir');
    const container = document.getElementById('shipping-rates-container');
    btn.disabled = true;
    btn.textContent = 'Mengambil data...';
    container.innerHTML = '<div class="shipping-loading">⏳ Menghitung tarif pengiriman...</div>';

    try {
        const res = await fetch('{{ route("checkout.shipping-rates") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ destination: city, weight: totalWeight })
        });
        const data = await res.json();

        if (data.success && data.rates.length > 0) {
            renderRates(data.rates);
        } else {
            container.innerHTML = '<div class="alert alert-warning">Tidak ada tarif tersedia untuk kota ini.</div>';
        }
    } catch (err) {
        container.innerHTML = '<div class="alert alert-danger">Gagal mengambil tarif. Coba lagi.</div>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3" stroke="currentColor" stroke-width="2"/><rect x="9" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="21" r="1" stroke="currentColor" stroke-width="2"/><circle cx="20" cy="21" r="1" stroke="currentColor" stroke-width="2"/></svg> Cek Ulang Ongkir`;
    }
}

function renderRates(rates) {
    const container = document.getElementById('shipping-rates-container');
    let html = '<div class="shipping-options">';
    rates.forEach((rate, i) => {
        html += `
        <div class="shipping-option" id="rate-${i}" onclick="selectRate(${i}, ${rate.cost}, '${escapeHtml(rate.courier)}', '${escapeHtml(rate.service)}')">
            <input type="radio" name="_rate_select" value="${i}">
            <div class="shipping-radio" id="radio-${i}"></div>
            <div class="shipping-info">
                <div class="shipping-name">${escapeHtml(rate.courier)}</div>
                <div class="shipping-service">${escapeHtml(rate.service)}</div>
                <div class="shipping-etd">🕐 Estimasi ${escapeHtml(rate.etd)}</div>
            </div>
            <div class="shipping-cost-val">Rp ${formatNum(rate.cost)}</div>
        </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
}

function selectRate(index, cost, courier, service) {
    // Deselect all
    document.querySelectorAll('.shipping-option').forEach(el => el.classList.remove('selected'));
    document.getElementById('rate-' + index).classList.add('selected');

    // Update hidden inputs
    document.getElementById('shipping-cost-input').value = cost;
    document.getElementById('courier-input').value = courier;
    document.getElementById('courier-service-input').value = service;

    // Update summary
    document.getElementById('display-ongkir').textContent = 'Rp ' + formatNum(cost);
    document.getElementById('display-ongkir').style.color = 'var(--color-text)';
    
    // Update total bayar
    updateTotalBayar(cost);

    // Enable pay button
    document.getElementById('btn-pay').disabled = false;
    selectedRate = { cost, courier, service };
}

function updateTotalBayar(shippingCost = 0) {
    const total = Math.max(0, subtotal + shippingCost - discountAmount);
    document.getElementById('display-total').textContent = 'Rp ' + formatNum(total);
}

async function applyVoucher() {
    const codeInput = document.getElementById('voucher-code');
    const code = codeInput.value.trim();
    if (!code) {
        alert('Masukkan kode voucher terlebih dahulu!');
        codeInput.focus();
        return;
    }

    const btn = document.getElementById('btn-apply-voucher');
    const msgDiv = document.getElementById('voucher-message');
    
    btn.disabled = true;
    btn.textContent = '...';
    msgDiv.style.display = 'none';

    try {
        const res = await fetch('{{ route("checkout.apply-voucher") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ code: code })
        });
        const data = await res.json();

        if (res.ok && data.success) {
            discountAmount = parseInt(data.voucher.discount);
            
            // Show active voucher UI
            document.getElementById('voucher-input-container').style.display = 'none';
            document.getElementById('voucher-active-container').style.display = 'flex';
            document.getElementById('active-voucher-label').textContent = data.voucher.code;
            
            // Update discount row
            document.getElementById('row-discount').style.display = 'flex';
            document.getElementById('display-discount').textContent = '-Rp ' + formatNum(discountAmount);
            
            // Display success message
            msgDiv.style.display = 'block';
            msgDiv.style.color = 'var(--color-primary)';
            msgDiv.textContent = data.message;
            
            // Update total
            const shippingCost = selectedRate ? selectedRate.cost : 0;
            updateTotalBayar(shippingCost);
        } else {
            msgDiv.style.display = 'block';
            msgDiv.style.color = '#dc3545';
            msgDiv.textContent = data.message || 'Gagal menerapkan voucher.';
        }
    } catch (err) {
        msgDiv.style.display = 'block';
        msgDiv.style.color = '#dc3545';
        msgDiv.textContent = 'Gagal memproses voucher. Coba lagi.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Gunakan';
    }
}

async function removeVoucher() {
    const msgDiv = document.getElementById('voucher-message');
    msgDiv.style.display = 'none';
    
    try {
        const res = await fetch('{{ route("checkout.remove-voucher") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        
        if (res.ok) {
            discountAmount = 0;
            
            // Reset UI
            document.getElementById('voucher-input-container').style.display = 'flex';
            document.getElementById('voucher-active-container').style.display = 'none';
            document.getElementById('voucher-code').value = '';
            
            document.getElementById('row-discount').style.display = 'none';
            
            msgDiv.style.display = 'block';
            msgDiv.style.color = 'var(--color-text-2)';
            msgDiv.textContent = 'Voucher dihapus.';
            
            // Update total
            const shippingCost = selectedRate ? selectedRate.cost : 0;
            updateTotalBayar(shippingCost);
        }
    } catch (err) {
        alert('Gagal menghapus voucher.');
    }
}

function formatNum(n) { return parseInt(n).toLocaleString('id-ID'); }
function escapeHtml(s) { return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

// Disable pay if city changes
document.getElementById('city')?.addEventListener('change', () => {
    document.getElementById('btn-pay').disabled = true;
    document.getElementById('display-ongkir').textContent = 'Belum dipilih';
    document.getElementById('display-ongkir').style.color = 'var(--color-text-3)';
    
    const shippingCost = 0;
    updateTotalBayar(shippingCost);
    
    document.getElementById('shipping-rates-container').innerHTML = '<div style="text-align:center;padding:20px;color:var(--color-text-3);font-size:14px;">Klik "Cek Ongkir" untuk memperbarui tarif.</div>';
    selectedRate = null;
});

// Form validation before submit
document.getElementById('checkout-form')?.addEventListener('submit', function(e) {
    if (!selectedRate) {
        e.preventDefault();
        alert('Pilih ekspedisi pengiriman terlebih dahulu!');
        return;
    }
    document.getElementById('btn-pay').textContent = '⏳ Memproses pembayaran...';
    document.getElementById('btn-pay').disabled = true;
});
</script>
@endpush
