<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'MitraSpace E-commerce — Belanja produk pilihan terbaik dengan harga terjangkau dan pengiriman cepat.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MitraSpace E-commerce') | MitraSpace</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body>

<!-- ====== NAVBAR ====== -->
<header class="navbar" id="navbar">
    <div class="container">
        <div class="navbar-inner">
            <!-- Logo -->
            <a href="{{ route('catalog.index') }}" class="navbar-brand" id="navbar-logo">
                <div class="brand-icon">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="10" fill="url(#brandGrad)"/>
                        <path d="M8 14h24M8 20h24M8 26h16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="30" cy="26" r="4" fill="white"/>
                        <defs>
                            <linearGradient id="brandGrad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6C63FF"/>
                                <stop offset="1" stop-color="#4ECDC4"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <span class="brand-text">Mitra<span class="brand-accent">Space</span></span>
            </a>

            <!-- Search -->
            <form class="navbar-search" action="{{ route('catalog.index') }}" method="GET" id="search-form">
                <div class="search-wrap">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <input type="text" name="search" id="search-input" class="search-input" placeholder="Cari produk..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </form>

            <!-- Right Actions -->
            <div class="navbar-actions">
                <a href="{{ route('tracking.index') }}" class="nav-link" id="nav-track">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M9 20H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M16 3v4M8 3v4M3 11h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="m16 19 2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="nav-label">Lacak</span>
                </a>

                <a href="{{ route('cart.index') }}" class="nav-cart" id="nav-cart">
                    <div class="cart-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        @php $cartCount = collect(session('cart', []))->sum('quantity'); @endphp
                        @if($cartCount > 0)
                        <span class="cart-badge" id="cart-badge">{{ $cartCount }}</span>
                        @else
                        <span class="cart-badge hidden" id="cart-badge">0</span>
                        @endif
                    </div>
                    <span class="nav-label">Keranjang</span>
                </a>

                @auth
                <a href="{{ route('orders.index') }}" class="nav-link" id="nav-orders">
                    <svg viewBox="0 0 24 24" fill="none" width="20" height="20" style="stroke: currentColor; stroke-width: 2;">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="3" y1="6" x2="21" y2="6" stroke-linecap="round"/>
                        <path d="M16 10a4 4 0 0 1-8 0" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="nav-label">Pesanan Saya</span>
                </a>
                <div class="nav-user" id="nav-user-profile">
                    <img src="{{ auth()->user()->avatar ?? 'https://www.gravatar.com/avatar/' . md5(auth()->user()->email) . '?d=mp' }}" alt="{{ auth()->user()->name }}" class="user-avatar">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn" title="Logout">
                            <svg viewBox="0 0 24 24" fill="none" class="logout-icon" width="20" height="20">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>
                </div>

                @else
                <a href="{{ route('login') }}" class="btn-login-google" id="btn-login">
                    <svg viewBox="0 0 24 24" class="google-icon" width="18" height="18">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                    </svg>
                    <span>Login</span>
                </a>
                @endauth
            </div>

            <!-- Hamburger -->
            <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<!-- Flash Messages -->
@if(session('success'))
<div class="flash flash-success" id="flash-msg">
    <svg viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2"/><polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2"/></svg>
    {{ session('success') }}
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif
@if(session('error'))
<div class="flash flash-error" id="flash-msg-error">
    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/></svg>
    {{ session('error') }}
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif
@if(session('info'))
<div class="flash flash-info" id="flash-msg-info">
    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/><path d="M12 8v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    {{ session('info') }}
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

<!-- Page Content -->
<main class="main-content" id="main-content">
    @yield('content')
</main>

<!-- ====== FOOTER ====== -->
<footer class="footer" id="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" width="36" height="36">
                        <rect width="40" height="40" rx="10" fill="url(#footerGrad)"/>
                        <path d="M8 14h24M8 20h24M8 26h16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="30" cy="26" r="4" fill="white"/>
                        <defs>
                            <linearGradient id="footerGrad" x1="0" y1="0" x2="40" y2="40">
                                <stop stop-color="#6C63FF"/>
                                <stop offset="1" stop-color="#4ECDC4"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span>Mitra<strong>Space</strong></span>
                </div>
                <p>Platform belanja online terpercaya dengan ribuan produk pilihan. Belanja mudah, aman, dan cepat.</p>
            </div>
            <div class="footer-links">
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="{{ route('catalog.index') }}">Katalog Produk</a></li>
                    <li><a href="{{ route('cart.index') }}">Keranjang Belanja</a></li>
                    <li><a href="{{ route('tracking.index') }}">Lacak Pesanan</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Dukungan</h4>
                <ul>
                    <li><a href="#">Cara Berbelanja</a></li>
                    <li><a href="#">Kebijakan Pengembalian</a></li>
                    <li><a href="#">Kontak Kami</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Pembayaran</h4>
                <div class="payment-logos">
                    <span class="payment-badge">DOKU</span>
                    <span class="payment-badge">Transfer</span>
                    <span class="payment-badge">QRIS</span>
                </div>
                <h4 style="margin-top: 1rem;">Pengiriman</h4>
                <div class="payment-logos">
                    <span class="payment-badge">JNE</span>
                    <span class="payment-badge">J&T</span>
                    <span class="payment-badge">SiCepat</span>
                    <span class="payment-badge">Anteraja</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} MitraSpace E-commerce. Semua hak dilindungi.</p>
            <p>Terintegrasi dengan <strong>Seller Center</strong> via Secure API</p>
        </div>
    </div>
</footer>

<script>
// Navbar scroll effect
window.addEventListener('scroll', () => {
    const nav = document.getElementById('navbar');
    nav.classList.toggle('scrolled', window.scrollY > 20);
});

// Hamburger menu
const hamburger = document.getElementById('hamburger');
const navbar    = document.getElementById('navbar');
hamburger?.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navbar.classList.toggle('mobile-open');
});

// Auto-dismiss flash messages
setTimeout(() => {
    ['flash-msg', 'flash-msg-error', 'flash-msg-info'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }
    });
}, 4000);
</script>

@stack('scripts')
</body>
</html>
