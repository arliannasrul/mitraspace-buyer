<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DokuService
{
    protected string $clientId;
    protected string $secretKey;
    protected bool $isProduction;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId     = config('services.doku.client_id', '');
        $this->secretKey    = config('services.doku.secret_key', '');
        $this->isProduction = config('services.doku.is_production', false);
        $this->baseUrl      = $this->isProduction
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    /**
     * Buat sesi pembayaran DOKU Checkout dan kembalikan payment URL.
     */
    public function createCheckoutSession(array $orderData): array
    {
        $invoiceNumber   = $orderData['invoice_number'];
        $amount          = (int) $orderData['amount'];
        $customerName    = $orderData['customer_name'];
        $customerEmail   = $orderData['customer_email'] ?? 'customer@mitraspace.com';
        $customerPhone   = $orderData['customer_phone'] ?? '';

        $requestId        = Str::uuid()->toString();
        $requestTimestamp = gmdate('Y-m-d\TH:i:s\Z');

        $requestBody = [
            'order' => [
                'amount'         => $amount,
                'invoice_number' => $invoiceNumber,
                'currency'       => 'IDR',
                'callback_url'   => config('services.doku.success_url'),
                'callback_url_cancel' => config('services.doku.failed_url'),
                'auto_redirect'  => true,
            ],
            'payment' => [
                'payment_due_date' => 60, // menit
            ],
            'customer' => [
                'name'  => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
            ],
        ];

        $requestBodyJson = json_encode($requestBody, JSON_UNESCAPED_SLASHES);
        $signature       = $this->generateSignature($requestId, $requestTimestamp, $requestBodyJson);

        $headers = [
            'Client-Id'         => $this->clientId,
            'Request-Id'        => $requestId,
            'Request-Timestamp' => $requestTimestamp,
            'Signature'         => 'HMACSHA256=' . $signature,
            'Content-Type'      => 'application/json',
        ];

        try {
            $ch = curl_init("{$this->baseUrl}/checkout/v1/payment");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $requestBodyJson,
                CURLOPT_HTTPHEADER     => $this->formatHeaders($headers),
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $responseBody = curl_exec($ch);
            $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($responseBody, true);

            Log::info('DokuService: checkout session', [
                'invoice' => $invoiceNumber,
                'amount'  => $amount,
                'status'  => $httpCode,
            ]);

            if ($httpCode === 200 && isset($responseData['response']['payment']['url'])) {
                return [
                    'success'     => true,
                    'payment_url' => $responseData['response']['payment']['url'],
                    'invoice'     => $invoiceNumber,
                ];
            }

            Log::warning('DokuService: unexpected response', ['response' => $responseData, 'http_code' => $httpCode]);

            return [
                'success' => false,
                'message' => $responseData['error']['message'] ?? 'Gagal membuat sesi pembayaran DOKU.',
            ];
        } catch (\Exception $e) {
            Log::error('DokuService: exception', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Koneksi ke DOKU gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Verifikasi signature dari webhook DOKU.
     * DOKU mengirim header: Signature, Client-Id, Request-Timestamp, Request-Id
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $signature       = $request->header('Signature', '');
        // Bersihkan prefix "HMACSHA256=" jika ada
        $signature       = str_replace('HMACSHA256=', '', $signature);

        $requestId       = $request->header('Request-Id', '');
        $requestTimestamp = $request->header('Request-Timestamp', '');
        $clientId        = $request->header('Client-Id', '');

        if ($clientId !== $this->clientId) {
            Log::warning('DokuService: webhook Client-Id mismatch');
            return false;
        }

        $rawBody       = $request->getContent();
        $digestBody    = base64_encode(hash('sha256', $rawBody, true));
        $requestTarget = $request->getPathInfo();

        $componentStr  = "Client-Id:{$this->clientId}\n"
            . "Request-Id:{$requestId}\n"
            . "Request-Timestamp:{$requestTimestamp}\n"
            . "Request-Target:{$requestTarget}\n"
            . "Digest:{$digestBody}";

        $expectedSignature = base64_encode(
            hash_hmac('sha256', $componentStr, $this->secretKey, true)
        );

        $valid = hash_equals($expectedSignature, $signature);

        if (!$valid) {
            Log::warning('DokuService: signature mismatch', [
                'expected' => $expectedSignature,
                'received' => $signature,
                'component_string' => $componentStr,
            ]);
        }

        return $valid;
    }

    /**
     * Generate signature untuk request ke DOKU API.
     */
    protected function generateSignature(string $requestId, string $requestTimestamp, string $requestBodyJson): string
    {
        $digestBody   = base64_encode(hash('sha256', $requestBodyJson, true));
        $componentStr = "Client-Id:{$this->clientId}\n"
            . "Request-Id:{$requestId}\n"
            . "Request-Timestamp:{$requestTimestamp}\n"
            . "Request-Target:/checkout/v1/payment\n"
            . "Digest:{$digestBody}";

        return base64_encode(
            hash_hmac('sha256', $componentStr, $this->secretKey, true)
        );
    }

    protected function formatHeaders(array $headers): array
    {
        return array_map(
            fn($key, $value) => "{$key}: {$value}",
            array_keys($headers),
            array_values($headers)
        );
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }
}
