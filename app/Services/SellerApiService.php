<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SellerApiService
{
    protected string $baseUrl;
    protected int $timeout = 15;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.seller_api.base_url', ''), '/');
    }

    /**
     * Ambil daftar produk dari Seller Center.
     */
    public function getProducts(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true', // Bypass Ngrok interstitial
                ])
                ->get("{$this->baseUrl}/api/ecommerce/products");

            if ($response->successful()) {
                $raw = $response->json('data', $response->json() ?? []);

                // Normalkan field name dari Seller Center ke format standar app
                return array_map(fn($p) => $this->normalizeProduct($p), $raw);
            }

            Log::warning('SellerApiService: getProducts failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('SellerApiService: getProducts exception', ['error' => $e->getMessage()]);
        }

        // Fallback ke mock data agar UI tetap bisa ditest
        return $this->getMockProducts();
    }

    /**
     * Normalisasi field produk dari berbagai format Seller Center.
     * Seller Center menggunakan: quantity, unit_price, dll.
     * App menggunakan: stock, price, dll.
     */
    protected function normalizeProduct(array $p): array
    {
        return [
            'id'          => $p['id'] ?? null,
            'name'        => $p['name'] ?? $p['product_name'] ?? '-',
            'sku'         => $p['sku'] ?? null,
            'category'    => $p['category'] ?? $p['category_name'] ?? null,
            'description' => $p['description'] ?? $p['notes'] ?? null,
            'price'       => (float) ($p['price'] ?? $p['unit_price'] ?? $p['harga'] ?? 0),
            'stock'       => (int)   ($p['stock'] ?? $p['quantity'] ?? $p['qty'] ?? $p['stok'] ?? 0),
            'weight'      => (float) ($p['weight'] ?? $p['berat'] ?? 0.5),
            'unit'        => $p['unit'] ?? 'pcs',
            'image_url'   => $p['image_url'] ?? $p['image'] ?? $p['foto'] ?? null,
        ];
    }

    /**
     * Kirim data order ke Seller Center setelah pembayaran berhasil.
     */
    public function submitOrder(array $payload): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->withHeaders(['ngrok-skip-browser-warning' => 'true'])
                ->post("{$this->baseUrl}/api/ecommerce/checkout", $payload);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::warning('SellerApiService: submitOrder failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'payload' => $payload,
            ]);

            return ['success' => false, 'message' => 'Seller API error: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error('SellerApiService: submitOrder exception', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Lacak status pesanan dari Seller Center.
     */
    public function trackOrder(string $orderNumber): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->withHeaders(['ngrok-skip-browser-warning' => 'true'])
                ->get("{$this->baseUrl}/api/ecommerce/tracking/{$orderNumber}");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json('data', $response->json())];
            }

            if ($response->status() === 404) {
                return ['success' => false, 'message' => 'Nomor pesanan tidak ditemukan.'];
            }

            return ['success' => false, 'message' => 'Gagal mengambil data tracking.'];
        } catch (\Exception $e) {
            Log::error('SellerApiService: trackOrder exception', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Tidak dapat terhubung ke server. Coba lagi nanti.'];
        }
    }

    /**
     * Mock products untuk development/fallback saat Ngrok tidak aktif.
     */
    protected function getMockProducts(): array
    {
        return [
            [
                'id'          => 1,
                'name'        => 'Laptop Gaming Pro X15',
                'price'       => 12500000,
                'stock'       => 10,
                'weight'      => 2.5,
                'category'    => 'Elektronik',
                'description' => 'Laptop gaming performa tinggi dengan GPU terbaru, layar 144Hz, dan RAM 16GB.',
                'image_url'   => null,
            ],
            [
                'id'          => 2,
                'name'        => 'Wireless Earbuds Premium',
                'price'       => 450000,
                'stock'       => 35,
                'weight'      => 0.2,
                'category'    => 'Aksesoris',
                'description' => 'TWS earbuds dengan noise cancelling aktif, baterai 30 jam, dan koneksi Bluetooth 5.3.',
                'image_url'   => null,
            ],
            [
                'id'          => 3,
                'name'        => 'Mechanical Keyboard RGB',
                'price'       => 850000,
                'stock'       => 20,
                'weight'      => 1.2,
                'category'    => 'Aksesoris',
                'description' => 'Keyboard mekanikal dengan switch Cherry MX Red, backlight RGB per-key, dan layout 80%.',
                'image_url'   => null,
            ],
            [
                'id'          => 4,
                'name'        => 'Monitor 4K Ultra HD 27"',
                'price'       => 4200000,
                'stock'       => 8,
                'weight'      => 5.0,
                'category'    => 'Elektronik',
                'description' => 'Monitor IPS 27 inci resolusi 4K, refresh rate 144Hz, dan panel HDR400.',
                'image_url'   => null,
            ],
            [
                'id'          => 5,
                'name'        => 'USB-C Hub 7-in-1',
                'price'       => 325000,
                'stock'       => 50,
                'weight'      => 0.15,
                'category'    => 'Aksesoris',
                'description' => 'Hub USB-C dengan port HDMI 4K, USB 3.0 x3, SD Card, TF Card, dan PD 100W.',
                'image_url'   => null,
            ],
            [
                'id'          => 6,
                'name'        => 'SSD Eksternal 1TB',
                'price'       => 1100000,
                'stock'       => 15,
                'weight'      => 0.08,
                'category'    => 'Storage',
                'description' => 'SSD portabel NVMe dengan kecepatan baca 2000MB/s, tahan air dan benturan.',
                'image_url'   => null,
            ],
        ];
    }
}
