<?php

namespace App\Http\Controllers;

use App\Services\DokuService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        protected DokuService     $doku,
        protected ShippingService $shipping
    ) {}

    public function index()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('catalog.index')->with('error', 'Keranjang belanja Anda kosong.');
        }

        // Clear active voucher on entering checkout
        session()->forget('applied_voucher');

        $subtotal    = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
        $totalWeight = array_sum(array_map(fn($i) => $i['weight'] * $i['quantity'], $cart));
        $cities      = $this->shipping->getPopularCities();

        return view('checkout.index', compact('cart', 'subtotal', 'totalWeight', 'cities'));
    }

    /**
     * AJAX endpoint: validasi dan gunakan kode voucher.
     */
    public function applyVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $voucher = \App\Models\Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak valid.',
            ], 422);
        }

        $cart = session('cart', []);
        $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));

        $errorMsg = '';
        if (!$voucher->isValidFor($subtotal, $errorMsg)) {
            return response()->json([
                'success' => false,
                'message' => $errorMsg,
            ], 422);
        }

        $discount = $voucher->calculateDiscount($subtotal);
        session(['applied_voucher' => [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'type' => $voucher->type,
            'value' => $voucher->value,
            'discount_amount' => $discount,
        ]]);

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil digunakan!',
            'voucher' => [
                'code' => $voucher->code,
                'discount' => $discount,
            ]
        ]);
    }

    /**
     * AJAX endpoint: hapus voucher dari session.
     */
    public function removeVoucher()
    {
        session()->forget('applied_voucher');

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dihapus.',
        ]);
    }

    /**
     * AJAX endpoint: hitung ongkir berdasarkan kota tujuan dan berat.
     */
    public function shippingRates(Request $request)
    {
        $request->validate([
            'destination' => 'required|string|max:100',
            'weight'      => 'nullable|numeric|min:0.1',
        ]);

        $cart   = session('cart', []);
        $weight = (float) $request->input('weight', 1.0);

        if (empty($weight) && !empty($cart)) {
            $weight = array_sum(array_map(fn($i) => $i['weight'] * $i['quantity'], $cart));
            $weight = max($weight, 0.1);
        }

        $rates = $this->shipping->getRates($request->input('destination'), $weight);

        return response()->json([
            'success' => true,
            'rates'   => $rates,
        ]);
    }

    /**
     * Proses checkout: buat sesi pembayaran DOKU dan redirect ke payment URL.
     */
    public function pay(Request $request)
    {
        $request->validate([
            'recipient_name'    => 'required|string|max:100',
            'recipient_phone'   => 'required|string|max:20',
            'address'           => 'required|string|max:500',
            'city'              => 'required|string|max:100',
            'courier'           => 'required|string',
            'courier_service'   => 'required|string',
            'shipping_cost'     => 'required|numeric|min:0',
            'customer_email'    => 'nullable|email|max:150',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('catalog.index')->with('error', 'Keranjang belanja kosong.');
        }

        $subtotal     = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart));
        $shippingCost = (int) $request->input('shipping_cost');
        
        // Hitung diskon voucher dari session
        $discountAmount = 0;
        $voucherCode = null;
        $sessionVoucher = session('applied_voucher');
        if ($sessionVoucher) {
            $voucher = \App\Models\Voucher::find($sessionVoucher['id']);
            if ($voucher && $voucher->isValidFor($subtotal)) {
                $discountAmount = (int) $voucher->calculateDiscount($subtotal);
                $voucherCode = $voucher->code;
            } else {
                session()->forget('applied_voucher');
            }
        }

        $totalAmount  = max(0, $subtotal + $shippingCost - $discountAmount);
        $invoiceNumber = 'INV-' . strtoupper(Str::random(8)) . '-' . date('YmdHis');

        // Simpan data order ke session untuk dipakai setelah webhook
        $orderData = [
            'invoice_number'  => $invoiceNumber,
            'amount'          => $totalAmount,
            'subtotal'        => $subtotal,
            'shipping_cost'   => $shippingCost,
            'discount_amount' => $discountAmount,
            'voucher_code'    => $voucherCode,
            'courier'         => $request->input('courier'),
            'courier_service' => $request->input('courier_service'),
            'customer_name'   => $request->input('recipient_name'),
            'customer_email'  => $request->input('customer_email', 'customer@mitraspace.com'),
            'customer_phone'  => $request->input('recipient_phone'),
            'shipping_address' => [
                'name'    => $request->input('recipient_name'),
                'phone'   => $request->input('recipient_phone'),
                'address' => $request->input('address'),
                'city'    => $request->input('city'),
            ],
            'items' => array_map(fn($item) => [
                'id'       => $item['id'],
                'name'     => $item['name'],
                'price'    => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['price'] * $item['quantity'],
            ], array_values($cart)),
        ];

        session(['pending_order' => $orderData]);

        // Simpan log pembayaran awal dengan status PENDING beserta seluruh detail order
        \App\Models\PaymentLog::create([
            'user_id'        => auth()->id(),
            'invoice_number' => $invoiceNumber,
            'amount'         => $totalAmount,
            'status'         => 'PENDING',
            'doku_reference' => '',
            'order_payload'  => json_encode($orderData),
        ]);

        // Buat sesi pembayaran DOKU
        $result = $this->doku->createCheckoutSession($orderData);

        if ($result['success']) {
            return redirect($result['payment_url']);
        }

        return back()->with('error', 'Gagal menghubungi payment gateway: ' . $result['message']);
    }
}
