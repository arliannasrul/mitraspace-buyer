<?php

namespace App\Http\Controllers;

use App\Models\PaymentLog;
use App\Services\DokuService;
use App\Services\SellerApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected DokuService      $doku,
        protected SellerApiService $sellerApi
    ) {}

    /**
     * Menerima notifikasi pembayaran dari DOKU Sandbox.
     * Route dikecualikan dari CSRF di VerifyCsrfToken.
     */
    public function dokuCallback(Request $request)
    {
        Log::info('DOKU Webhook received', [
            'headers' => $request->headers->all(),
            'body'    => $request->getContent(),
        ]);

        // Verifikasi signature DOKU untuk keamanan
        if (!$this->doku->verifyWebhookSignature($request)) {
            Log::warning('DOKU Webhook: invalid signature, rejecting request');
            return response()->json(['status' => 'UNAUTHORIZED'], 401);
        }

        $payload       = $request->json()->all();
        $invoiceNumber = data_get($payload, 'order.invoice_number');
        $transStatus   = data_get($payload, 'transaction.status');
        $amount        = data_get($payload, 'order.amount');
        $dokuReference = data_get($payload, 'transaction.id', data_get($payload, 'transaction.reference'));

        // Catat log pembayaran ke database lokal
        $log = PaymentLog::firstOrCreate(
            ['invoice_number' => $invoiceNumber],
            [
                'amount'         => $amount,
                'status'         => $transStatus,
                'doku_reference' => $dokuReference,
                'order_payload'  => json_encode($payload),
            ]
        );

        if (!$log->wasRecentlyCreated) {
            // Update status jika sudah ada
            $log->update([
                'status'         => $transStatus,
                'doku_reference' => $dokuReference,
            ]);
        }

        // Jika pembayaran sukses, forward order ke Seller Center
        if (in_array(strtoupper($transStatus), ['SUCCESS', 'PAID', 'SETTLEMENT'])) {
            Log::info("DOKU Webhook: payment SUCCESS for invoice {$invoiceNumber}, forwarding to Seller Center");

            // Ambil data order yang tersimpan di log
            $orderPayload = json_decode($log->order_payload, true) ?? [];

            // Bentuk payload untuk Seller Center
            $sellerPayload = $this->buildSellerPayload($invoiceNumber, $amount, $dokuReference, $orderPayload);

            $result = $this->sellerApi->submitOrder($sellerPayload);

            if ($result['success']) {
                Log::info("DOKU Webhook: order successfully submitted to Seller Center", ['invoice' => $invoiceNumber]);
                $sellerOrderNumber = data_get($result, 'data.data.order_number');
                $log->update([
                    'status'              => 'FORWARDED_TO_SELLER',
                    'seller_order_number' => $sellerOrderNumber,
                ]);
            } else {
                Log::error("DOKU Webhook: failed to submit order to Seller Center", [
                    'invoice' => $invoiceNumber,
                    'error'   => $result['message'],
                ]);
            }
        }

        // DOKU mengharapkan response 200 OK
        return response()->json(['status' => 'OK'], 200);
    }

    protected function buildSellerPayload(string $invoiceNumber, $amount, $dokuReference, array $orderPayload): array
    {
        // 1. Jika orderPayload adalah local orderData format (memiliki key 'customer_name' atau 'shipping_address')
        if (isset($orderPayload['customer_name']) || isset($orderPayload['shipping_address'])) {
            $items = array_map(fn($item) => [
                'id'       => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
            ], $orderPayload['items'] ?? []);

            $cityName = $orderPayload['shipping_address']['city'] ?? 'Jakarta';
            $cityId = $this->getCityId($cityName);

            return [
                'customer_name'         => $orderPayload['customer_name'],
                'customer_phone'        => $orderPayload['customer_phone'],
                'customer_address'      => $orderPayload['shipping_address']['address'] ?? '',
                'destination_city_id'   => $cityId,
                'destination_city_name' => $cityName,
                'courier'               => strtolower($orderPayload['courier'] ?? 'jne'),
                'shipping_service'      => $orderPayload['courier_service'] ?? 'REG',
                'shipping_cost'         => (int) ($orderPayload['shipping_cost'] ?? 0),
                'items'                 => $items,
            ];
        }

        // 2. Fallback jika data local tidak ada (misal dari webhook murni tanpa DB log awal)
        return [
            'customer_name'         => data_get($orderPayload, 'customer.name') ?? 'Guest Customer',
            'customer_phone'        => data_get($orderPayload, 'customer.phone') ?? '081234567890',
            'customer_address'      => 'Alamat default (webhook)',
            'destination_city_id'   => 1,
            'destination_city_name' => 'Jakarta',
            'courier'               => 'jne',
            'shipping_service'      => 'REG',
            'shipping_cost'         => 15000,
            'items'                 => [
                ['id' => 1, 'quantity' => 1] // dummy item
            ]
        ];
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
}
