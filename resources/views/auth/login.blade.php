@extends('layouts.app')

@section('title', 'Masuk ke Akun — MitraSpace')

@section('content')
<div class="login-container">
    <div class="login-card">
        <!-- Logo Brand -->
        <div class="login-logo-wrap">
            <div class="brand-icon" style="width: 52px; height: 52px; margin: 0 auto 12px;">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="10" fill="url(#brandGradLogin)"/>
                    <path d="M8 14h24M8 20h24M8 26h16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="30" cy="26" r="4" fill="white"/>
                    <defs>
                        <linearGradient id="brandGradLogin" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#6C63FF"/>
                            <stop offset="1" stop-color="#4ECDC4"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <h2>Mitra<span style="color: var(--color-primary)">Space</span></h2>
            <p class="login-subtitle">Selamat datang kembali! Silakan masuk ke akun Anda.</p>
        </div>

        <hr class="login-divider">

        <div class="login-body">
            <p class="login-info-text">
                Kami menggunakan **Google Sign-In** untuk memberikan pengalaman belanja yang aman, cepat, dan tanpa sandi.
            </p>

            <!-- Google Login Button -->
            <a href="{{ route('auth.google') }}" class="btn-login-google-large">
                <svg viewBox="0 0 24 24" class="google-icon" width="22" height="22">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
                <span>Masuk dengan Google</span>
            </a>
        </div>

        <div class="login-footer">
            <a href="{{ route('catalog.index') }}" class="back-to-catalog">
                <svg viewBox="0 0 24 24" fill="none" width="16" height="16"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali ke Katalog
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.login-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 68px - 280px);
    padding: var(--space-2xl) 0;
}
.login-card {
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    box-shadow: var(--shadow-lg);
    width: 100%;
    max-width: 420px;
    padding: var(--space-2xl);
    text-align: center;
    animation: slideDown .3s ease;
}
.login-logo-wrap h2 {
    font-size: 1.6rem;
    font-weight: 800;
    margin-bottom: 6px;
    font-family: var(--font-heading);
}
.login-subtitle {
    font-size: 13px;
    color: var(--color-text-3);
}
.login-divider {
    border: none;
    border-top: 1px solid var(--color-border);
    margin: var(--space-lg) 0;
}
.login-info-text {
    font-size: 13px;
    color: var(--color-text-2);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}
.btn-login-google-large {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    height: 48px;
    background: var(--color-surface);
    border: 1.5px solid var(--color-border);
    border-radius: var(--radius-md);
    color: var(--color-text-2);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: var(--transition-bounce);
    box-shadow: var(--shadow-sm);
}
.btn-login-google-large:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
    color: var(--color-primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.login-footer {
    margin-top: var(--space-lg);
}
.back-to-catalog {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--color-text-3);
    font-weight: 500;
}
.back-to-catalog:hover {
    color: var(--color-primary);
}
</style>
@endpush
