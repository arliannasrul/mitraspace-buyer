<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $originCity;

    public function __construct()
    {
        $this->apiKey     = config('services.kiriminaja.api_key', '');
        $this->baseUrl    = config('services.kiriminaja.base_url', 'https://tdev.kiriminaja.com');
        $this->originCity = config('services.kiriminaja.origin_city', 'Jakarta');
    }

    /**
     * Dapatkan tarif pengiriman.
     * Jika API key kosong atau request gagal, gunakan mock rates.
     */
    public function getRates(string $destination, float $weight = 1.0): array
    {
        if (empty($this->apiKey) || config('services.kiriminaja.use_mock', true)) {
            Log::info('ShippingService: using mock rates (no API key or mock mode)');
            return $this->getMockRates($destination, $weight);
        }

        return $this->getLiveRates($destination, $weight);
    }

    protected function getLiveRates(string $destination, float $weight): array
    {
        try {
            $response = Http::timeout(15)
                ->withToken($this->apiKey)
                ->acceptJson()
                ->post("{$this->baseUrl}/api/check-ongkir", [
                    'origin'      => $this->originCity,
                    'destination' => $destination,
                    'weight'      => $weight,
                ]);

            if ($response->successful()) {
                $data  = $response->json('data', []);
                $rates = [];

                foreach ($data as $courier) {
                    $rates[] = [
                        'courier'      => $courier['courier_name'] ?? $courier['code'] ?? 'Unknown',
                        'service'      => $courier['service_name'] ?? $courier['service'] ?? 'Regular',
                        'cost'         => (int) ($courier['price'] ?? $courier['cost'] ?? 0),
                        'etd'          => $courier['etd'] ?? $courier['estimated_day'] ?? '2-3',
                        'courier_code' => strtolower($courier['code'] ?? ''),
                    ];
                }

                return $rates ?: $this->getMockRates($destination, $weight);
            }

            Log::warning('ShippingService: API failed', ['status' => $response->status()]);
        } catch (\Exception $e) {
            Log::error('ShippingService: exception', ['error' => $e->getMessage()]);
        }

        return $this->getMockRates($destination, $weight);
    }

    /**
     * Mock tarif pengiriman untuk development & fallback.
     * Tarif disesuaikan berdasarkan berat.
     */
    public function getMockRates(string $destination, float $weight = 1.0): array
    {
        $multiplier = max(1, ceil($weight));

        return [
            [
                'courier'      => 'JNE',
                'service'      => 'REG (Reguler)',
                'cost'         => 15000 * $multiplier,
                'etd'          => '2-3 hari',
                'courier_code' => 'jne',
            ],
            [
                'courier'      => 'JNE',
                'service'      => 'YES (Yakin Esok Sampai)',
                'cost'         => 25000 * $multiplier,
                'etd'          => '1 hari',
                'courier_code' => 'jne',
            ],
            [
                'courier'      => 'J&T Express',
                'service'      => 'EZ (Ekonomis)',
                'cost'         => 13000 * $multiplier,
                'etd'          => '2-4 hari',
                'courier_code' => 'jnt',
            ],
            [
                'courier'      => 'SiCepat',
                'service'      => 'BEST (Besok Tiba)',
                'cost'         => 20000 * $multiplier,
                'etd'          => '1-2 hari',
                'courier_code' => 'sicepat',
            ],
            [
                'courier'      => 'Anteraja',
                'service'      => 'Regular',
                'cost'         => 12000 * $multiplier,
                'etd'          => '2-4 hari',
                'courier_code' => 'anteraja',
            ],
        ];
    }

    /**
     * Daftar kota tujuan yang umum untuk dropdown checkout.
     */
    public function getPopularCities(): array
    {
        return [
            'Jakarta'    => 'Jakarta',
            'Surabaya'   => 'Surabaya',
            'Bandung'    => 'Bandung',
            'Medan'      => 'Medan',
            'Semarang'   => 'Semarang',
            'Makassar'   => 'Makassar',
            'Palembang'  => 'Palembang',
            'Tangerang'  => 'Tangerang',
            'Depok'      => 'Depok',
            'Bekasi'     => 'Bekasi',
            'Bogor'      => 'Bogor',
            'Batam'      => 'Batam',
            'Pekanbaru'  => 'Pekanbaru',
            'Balikpapan' => 'Balikpapan',
            'Malang'     => 'Malang',
            'Yogyakarta' => 'Yogyakarta',
            'Denpasar'   => 'Denpasar',
            'Padang'     => 'Padang',
            'Samarinda'  => 'Samarinda',
            'Banjarmasin' => 'Banjarmasin',
        ];
    }
}
