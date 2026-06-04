<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KosSeeder extends Seeder
{
    private array $fasilitasMap = [
        'ac'                     => 'AC',
        'wifi'                   => 'WiFi',
        'kasur'                  => 'Kasur',
        'lemari baju'            => 'Lemari Baju',
        'meja'                   => 'Meja',
        'kursi'                  => 'Kursi',
        'k. mandi dalam'         => 'K. Mandi Dalam',
        'k. mandi luar'          => 'K. Mandi Luar',
        'air panas'              => 'Air Panas',
        'tv'                     => 'TV',
        'kulkas'                 => 'Kulkas',
        'mesin cuci'             => 'Mesin Cuci',
        'kipas angin'            => 'Kipas Angin',
        'dapur pribadi'          => 'Dapur Pribadi',
        'dapur'                  => 'Dapur',
        'cctv'                   => 'CCTV',
        'kartu akses'            => 'Kartu Akses',
        'pengurus kos'           => 'Pengurus Kos',
        'penjaga kos'            => 'Penjaga Kos',
        'parkir mobil'           => 'Parkir Mobil',
        'parkir motor'           => 'Parkir Motor',
        'laundry'                => 'Laundry',
        'mushola'                => 'Mushola',
        'balcon'                 => 'Balkon',
        'taman'                  => 'Taman',
    ];

    public function run(): void
    {
        $this->command->info('Memulai seeding data minimalis...');
        DB::table('kos')->truncate();

        $jsonPath = database_path('data/kos-data.json');
        if (!file_exists($jsonPath)) return;

        $raw = json_decode(file_get_contents($jsonPath), true);
        $processed = [];
        $skipped = 0;
        $urlSeen = [];

        foreach ($raw as $item) {
            $price = $this->parseHarga($item['price'] ?? null);
            if ($price === null || $price < 300000 || $price > 15000000) {
                $skipped++;
                continue;
            }

            $url = trim($item['url'] ?? '');
            if ($url && isset($urlSeen[$url])) {
                $skipped++;
                continue;
            }
            if ($url) $urlSeen[$url] = true;

            $facilitiesClean = $this->cleanFasilitas($item['all_facilities_bs'] ?? '');
            $roomName        = $this->bersihkanTeks($item['room_name'] ?? '');
            $region          = trim($item['region'] ?? '');
            $tipeKos         = $this->normalisasiTipe($item['tipe_kos'] ?? '');

            $processed[] = [
                'room_name'      => $roomName,
                'region'         => $region,
                'price'          => $price,
                'price_display'  => $item['price_display'] ?? ('Rp ' . number_format($price, 0, ',', '.')),
                'all_facilities' => $facilitiesClean,
                'tipe_kos'       => $tipeKos,
                'url'            => $url ?: null,
                'image_url'      => $item['image_url'] ?? null,
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        foreach (array_chunk($processed, 100) as $chunk) {
            DB::table('kos')->insert($chunk);
        }

        $this->command->info('Selesai! Tersimpan : ' . count($processed));
    }

    private function parseHarga(mixed $val): ?int
    {
        if (empty($val) || $val === 'Not found') return null;
        $clean = str_replace(',', '', explode('-', preg_replace('/[Rp\.\s]/', '', (string) $val))[0]);
        return is_numeric(trim($clean)) ? (int) trim($clean) : null;
    }

    private function cleanFasilitas(?string $val): string
    {
        if (empty($val)) return '';
        $items = array_map('trim', explode(';', $val));
        $result = [];
        $seen = [];

        foreach ($items as $item) {
            if (empty($item) || preg_match('/^[\d\.]+\s*x\s*[\d\.]+/i', $item) || preg_match('/[\x80-\xFF]|â€/', $item)) continue;
            
            $key = strtolower(trim($item));
            $normalized = $this->fasilitasMap[$key] ?? ucwords(strtolower($item));
            
            if (isset($seen[strtolower($normalized)])) continue;
            $seen[strtolower($normalized)] = true;
            $result[] = $normalized;
        }
        return implode('; ', $result);
    }

    private function bersihkanTeks(string $val): string {
        return trim(preg_replace('/\s+/', ' ', $val));
    }

    private function normalisasiTipe(string $val): string {
        return in_array($val, ['Kos Campur', 'Kos Putra', 'Kos Putri']) ? $val : 'Kos Campur';
    }
}