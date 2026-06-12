<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — MitraSpace E-commerce
|--------------------------------------------------------------------------
*/

// ---- Katalog ----
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{id}', [CatalogController::class, 'show'])->name('catalog.show');

// ---- Keranjang & Checkout (Wajib Login) ----
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/shipping-rates', [CheckoutController::class, 'shippingRates'])->name('checkout.shipping-rates');
    Route::post('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
});

// Keranjang Add (Dicek manual di controller agar tidak error redirect POST)
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

// ---- Pembayaran (Callback dari DOKU) ----
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');

// ---- Lacak Pesanan ----
Route::get('/track', [TrackingController::class, 'index'])->name('tracking.index');
Route::post('/track', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/track/{orderNumber}', [TrackingController::class, 'show'])->name('tracking.show');

// ---- Webhook DOKU (dikecualikan dari CSRF di VerifyCsrfToken) ----
Route::post('/webhook/doku', [WebhookController::class, 'dokuCallback'])->name('webhook.doku');

// ---- Autentikasi Google OAuth ----
Route::get('/login', [\App\Http\Controllers\Auth\GoogleController::class, 'showLoginForm'])->name('login');
Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback']);
Route::post('/logout', [\App\Http\Controllers\Auth\GoogleController::class, 'logout'])->name('logout');
