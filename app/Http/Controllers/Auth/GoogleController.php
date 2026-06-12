<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('catalog.index');
        }
        return view('auth.login');
    }

    /**
     * Redirect ke Google Sign-In.
     */
    public function redirectToGoogle()
    {
        // Simpan intended URL jika user datang dari checkout
        if (url()->previous() === route('checkout.index')) {
            session(['url.intended' => route('checkout.index')]);
        }
        
        return Socialite::driver('google')->redirect();
    }

    /**
     * Tangani Callback dari Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Cari user berdasarkan google_id atau email
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // Update google_id dan avatar jika sebelumnya mendaftar biasa
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            } else {
                // Buat user baru (Customer/Buyer)
                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                    'password'  => null, // Password nullable untuk login Google
                ]);
            }

            Auth::login($user, true);

            Log::info('Google OAuth: user logged in successfully', ['email' => $user->email]);

            // Redirect ke intended URL atau halaman utama catalog
            return redirect()->intended(route('catalog.index'));

        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect()->route('catalog.index')->with('error', 'Gagal autentikasi menggunakan Google: ' . $e->getMessage());
        }
    }

    /**
     * Log out user.
     */
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('catalog.index')->with('info', 'Anda telah berhasil logout.');
    }
}
