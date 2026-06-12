<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function success(Request $request, \App\Services\SellerApiService $sellerApi)
    {
        $order = session('pending_order');

        if ($order) {
            $invoiceNumber = $order['invoice_number'];

            // Periksa apakah order ini sudah pernah dikirim ke Seller Center
            $log = \App\Models\PaymentLog::where('invoice_number', $invoiceNumber)->first();

            if (!$log || $log->status !== 'FORWARDED_TO_SELLER') {
                // Pastikan log ada di database
                if (!$log) {
                    $log = \App\Models\PaymentLog::create([
                        'user_id'        => auth()->id(),
                        'invoice_number' => $invoiceNumber,
                        'amount'         => $order['amount'],
                        'status'         => 'SUCCESS',
                        'doku_reference' => 'LOCAL_FALLBACK_' . time(),
                        'order_payload'  => json_encode($order),
                    ]);
                } else {
                    $log->update([
                        'user_id'        => $log->user_id ?: auth()->id(),
                        'status'         => 'SUCCESS',
                        'doku_reference' => $log->doku_reference ?: 'LOCAL_FALLBACK_' . time(),
                    ]);
                }

                // Map data order ke format payload yang diharapkan Seller Center
                $items = array_map(fn($item) => [
                    'id'       => (int) $item['id'],
                    'quantity' => (int) $item['quantity'],
                ], $order['items'] ?? []);

                $cityName = $order['shipping_address']['city'] ?? 'Jakarta';
                $cityId = $this->getCityId($cityName);

                $sellerPayload = [
                    'customer_name'         => $order['customer_name'],
                    'customer_phone'        => $order['customer_phone'],
                    'customer_address'      => $order['shipping_address']['address'] ?? '',
                    'destination_city_id'   => $cityId,
                    'destination_city_name' => $cityName,
                    'courier'               => strtolower($order['courier'] ?? 'jne'),
                    'shipping_service'      => $order['courier_service'] ?? 'REG',
                    'shipping_cost'         => (int) ($order['shipping_cost'] ?? 0),
                    'items'                 => $items,
                ];

                $result = $sellerApi->submitOrder($sellerPayload);

                if ($result['success']) {
                    \Illuminate\Support\Facades\Log::info("PaymentController success redirect: order successfully submitted to Seller Center", ['invoice' => $invoiceNumber]);
                    $sellerOrderNumber = data_get($result, 'data.data.order_number');
                    $log->update([
                        'status'              => 'FORWARDED_TO_SELLER',
                        'seller_order_number' => $sellerOrderNumber,
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::error("PaymentController success redirect: failed to submit order to Seller Center", [
                        'invoice' => $invoiceNumber,
                        'error'   => $result['message'],
                    ]);
                }
            }
        }

        // Bersihkan cart setelah berhasil bayar
        session()->forget('cart');

        return view('payment.success', compact('order'));
    }

    protected function getCityId(string $cityName): int
    {
        $popularCities = [
            'Jakarta'    => 1,
            'Surabaya'   => 2,
            'Bandung'    => 3,
            'Medan'      => 4,
            'Semarang'   => 5,
            'Makassar'   => 6,
            'Palembang'  => 7,
            'Tangerang'  => 8,
            'Depok'      => 9,
            'Bekasi'     => 10,
            'Bogor'      => 11,
            'Batam'      => 12,
            'Pekanbaru'  => 13,
            'Balikpapan' => 14,
            'Malang'     => 15,
            'Yogyakarta' => 16,
            'Denpasar'   => 17,
            'Padang'     => 18,
            'Samarinda'  => 19,
            'Banjarmasin' => 20,
        ];

        return $popularCities[$cityName] ?? 99;
    }

    public function failed(Request $request)
    {
        $order  = session('pending_order');
        $reason = $request->query('reason', 'Pembayaran dibatalkan atau gagal.');

        return view('payment.failed', compact('order', 'reason'));
    }
}
