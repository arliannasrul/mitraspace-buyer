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
        $this->apiKey     = config('services.rajaongkir.api_key', '');
        $this->baseUrl    = config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1');
        $this->originCity = config('services.rajaongkir.origin_city', 'Jakarta');
    }

    /**
     * Dapatkan tarif pengiriman.
     * Jika API key kosong atau request gagal, gunakan mock rates.
     */
    public function getRates(string $destination, float $weight = 1.0): array
    {
        if (empty($this->apiKey) || config('services.rajaongkir.use_mock', true)) {
            Log::info('ShippingService: using mock rates (no API key or mock mode)');
            return $this->getMockRates($destination, $weight);
        }

        return $this->getLiveRates($destination, $weight);
    }

    protected function getLiveRates(string $destination, float $weight): array
    {
        try {
            $originId = $this->getRajaOngkirCityId($this->originCity) ?: 152; // default Jakarta Barat (152)
            $destinationId = $this->getRajaOngkirCityId($destination);

            if (!$destinationId) {
                Log::warning("ShippingService: City '{$destination}' not found in RajaOngkir mapping. Falling back to mock.");
                return $this->getMockRates($destination, $weight);
            }

            // RajaOngkir Starter accepts weight in grams. 1 kg = 1000 grams.
            $weightInGrams = (int) ($weight * 1000);
            $couriers = ['jne', 'pos', 'tiki'];
            $rates = [];

            foreach ($couriers as $courier) {
                try {
                    $response = Http::timeout(10)
                        ->withHeaders([
                            'key' => $this->apiKey,
                        ])
                        ->asForm()
                        ->post("{$this->baseUrl}/calculate/domestic-cost", [
                            'origin'      => $originId,
                            'destination' => $destinationId,
                            'weight'      => $weightInGrams,
                            'courier'     => $courier,
                        ]);

                    if ($response->successful()) {
                        $results = $response->json('data', []);
                        foreach ($results as $service) {
                            $rates[] = [
                                'courier'      => $service['name'] ?? strtoupper($courier),
                                'service'      => ($service['service'] ?? 'REG') . ' (' . ($service['description'] ?? 'Reguler') . ')',
                                'cost'         => (int) ($service['cost'] ?? 0),
                                'etd'          => ($service['etd'] ?? '2-3') . (str_contains($service['etd'] ?? '', 'day') || str_contains($service['etd'] ?? '', 'hari') ? '' : ' hari'),
                                'courier_code' => strtolower($service['code'] ?? $courier),
                            ];
                        }
                    } else {
                        Log::warning("ShippingService: RajaOngkir API failed for {$courier}", [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ]);
                    }
                } catch (\Exception $ex) {
                    Log::error("ShippingService: RajaOngkir exception for courier {$courier}", ['error' => $ex->getMessage()]);
                }
            }

            return $rates ?: $this->getMockRates($destination, $weight);
        } catch (\Exception $e) {
            Log::error('ShippingService: RajaOngkir general exception', ['error' => $e->getMessage()]);
        }

        return $this->getMockRates($destination, $weight);
    }

    /**
     * Memetakan nama kota ke ID kota RajaOngkir Starter
     */
    private function getRajaOngkirCityId(string $cityName): ?int
    {
        $map = [
            'jakarta'     => 152, // Jakarta Barat
            'surabaya'    => 444,
            'bandung'     => 23,
            'medan'       => 278,
            'semarang'    => 399,
            'makassar'    => 246,
            'palembang'   => 327,
            'tangerang'   => 457,
            'depok'       => 115,
            'bekasi'      => 55,
            'bogor'       => 79,
            'batam'       => 48,
            'pekanbaru'   => 350,
            'balikpapan'  => 19,
            'malang'      => 256,
            'yogyakarta'  => 501,
            'denpasar'    => 114,
            'padang'      => 318,
            'samarinda'   => 387,
            'banjarmasin' => 36,
        ];

        return $map[strtolower(trim($cityName))] ?? null;
    }

    /**
     * Mock tarif pengiriman untuk development & fallback.
     * Tarif disesuaikan berdasarkan berat.
     */
    public function getMockRates(string $destination, float $weight = 1.0): array
    {
        $multiplier = max(1, ceil($weight));

        // Buat variasi tarif dinamis berdasarkan nama kota tujuan secara deterministik
        // Menggunakan crc32 hash untuk menghasilkan faktor jarak antara 1 sampai 10
        $baseDistanceFactor = (abs(crc32(strtolower($destination))) % 10) + 1;
        $baseCost = 8000 + ($baseDistanceFactor * 2000); // Tarif dasar berkisar 10.000 s/d 28.000

        return [
            [
                'courier'      => 'JNE',
                'service'      => 'REG (Reguler)',
                'cost'         => $baseCost * $multiplier,
                'etd'          => '2-3 hari',
                'courier_code' => 'jne',
            ],
            [
                'courier'      => 'JNE',
                'service'      => 'YES (Yakin Esok Sampai)',
                'cost'         => ($baseCost + 10000) * $multiplier,
                'etd'          => '1 hari',
                'courier_code' => 'jne',
            ],
            [
                'courier'      => 'J&T Express',
                'service'      => 'EZ (Ekonomis)',
                'cost'         => max(9000, ($baseCost - 2000)) * $multiplier,
                'etd'          => '2-4 hari',
                'courier_code' => 'jnt',
            ],
            [
                'courier'      => 'SiCepat',
                'service'      => 'BEST (Besok Tiba)',
                'cost'         => ($baseCost + 5000) * $multiplier,
                'etd'          => '1-2 hari',
                'courier_code' => 'sicepat',
            ],
            [
                'courier'      => 'Anteraja',
                'service'      => 'Regular',
                'cost'         => max(8000, ($baseCost - 3000)) * $multiplier,
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
