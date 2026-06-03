<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * KosSeeder — Seeder dengan Preprocessing Data Lengkap
 *
 * SEMUA preprocessing dilakukan di file ini sebelum data masuk database.
 * Lihat komentar [PP-1] sampai [PP-14] untuk lokasi tiap proses.
 */
class KosSeeder extends Seeder
{
    // =========================================================
    // BARIS 18-80: KAMUS NORMALISASI FASILITAS
    // Tujuan: menyamakan penulisan yang tidak konsisten di dataset
    // Contoh: "balcon" (typo) → "Balkon" (benar)
    // =========================================================
    private array $fasilitasMap = [
        'ac'                     => 'AC',
        'wifi'                   => 'WiFi',
        'kasur'                  => 'Kasur',
        'bantal'                 => 'Bantal',
        'guling'                 => 'Guling',
        'lemari baju'            => 'Lemari Baju',
        'meja'                   => 'Meja',
        'kursi'                  => 'Kursi',
        'cermin'                 => 'Cermin',
        'meja rias'              => 'Meja Rias',
        'meja makan'             => 'Meja Makan',
        'k. mandi dalam'         => 'K. Mandi Dalam',
        'k. mandi luar'          => 'K. Mandi Luar',
        'kloset duduk'           => 'Kloset Duduk',
        'kloset jongkok'         => 'Kloset Jongkok',
        'shower'                 => 'Shower',
        'bathtub'                => 'Bathtub',
        'bak mandi'              => 'Bak Mandi',
        'ember mandi'            => 'Ember Mandi',
        'air panas'              => 'Air Panas',
        'wastafel'               => 'Wastafel',
        'tv kabel'               => 'TV Kabel',
        'tv'                     => 'TV',
        'kulkas'                 => 'Kulkas',
        'mesin cuci'             => 'Mesin Cuci',
        'kipas angin'            => 'Kipas Angin',
        'rice cooker'            => 'Rice Cooker',
        'dispenser'              => 'Dispenser',
        'microwave'              => 'Microwave',
        'dapur pribadi'          => 'Dapur Pribadi',
        'dapur'                  => 'Dapur',
        'r. makan'               => 'R. Makan',
        'r. tamu'                => 'R. Tamu',
        'r. keluarga'            => 'R. Keluarga',
        'r. santai'              => 'R. Santai',
        'r. jemur'               => 'R. Jemur',
        'r. cuci'                => 'R. Cuci',
        'jemuran'                => 'Jemuran',
        'sofa'                   => 'Sofa',
        'locker'                 => 'Locker',
        'cctv'                   => 'CCTV',
        'kartu akses'            => 'Kartu Akses',
        'pengurus kos'           => 'Pengurus Kos',
        'penjaga kos'            => 'Penjaga Kos',
        'parkir mobil'           => 'Parkir Mobil',
        'parkir motor & sepeda'  => 'Parkir Motor & Sepeda',
        'parkir motor'           => 'Parkir Motor',
        'parkir sepeda'          => 'Parkir Sepeda',
        'laundry'                => 'Laundry',
        'mushola'                => 'Mushola',
        'cleaning service'       => 'Cleaning Service',
        'jual makanan'           => 'Jual Makanan',
        'balcon'                 => 'Balkon',
        'taman'                  => 'Taman',
        'gazebo'                 => 'Gazebo',
        'rooftop'                => 'Rooftop',
        'joglo'                  => 'Joglo',
        'jendela'                => 'Jendela',
        'ventilasi'              => 'Ventilasi',
        'termasuk listrik'       => 'Termasuk Listrik',
        'tidak termasuk listrik' => 'Tidak Termasuk Listrik',
    ];

    // =========================================================
    // BARIS 82: METHOD UTAMA run()
    // Alur: baca JSON → loop tiap baris → preprocessing → insert DB
    // =========================================================
    public function run(): void
    {
        $this->command->info('Memulai seeding dengan preprocessing...');
        DB::table('kos')->truncate();

        $jsonPath = database_path('data/kos-data.json');
        if (!file_exists($jsonPath)) {
            $this->command->error('File tidak ditemukan: ' . $jsonPath);
            return;
        }

        $raw = json_decode(file_get_contents($jsonPath), true);
        $this->command->info('Data mentah: ' . count($raw) . ' baris');

        $processed = [];
        $skipped   = 0;
        $urlSeen   = [];

        foreach ($raw as $item) {

            // ── [PP-1] BARIS 101-106: PARSING HARGA → integer ─────
            // Mengubah "Rp1.500.000" atau "Rp700.000-Rp900.000"
            // menjadi integer. Baris dengan harga tidak valid dibuang.
            // Outlier di bawah 300rb atau di atas 15jt juga dibuang.
            $price = $this->parseHarga($item['price'] ?? null);
            if ($price === null || $price < 300000 || $price > 15000000) {
                $skipped++;
                continue;
            }

            // ── [PP-2] BARIS 109-115: DEDUPLIKASI berdasarkan URL ──
            // Dataset memiliki 174 baris duplikat berdasarkan URL.
            // Baris duplikat dibuang, hanya yang pertama disimpan.
            $url = trim($item['url'] ?? '');
            if ($url && isset($urlSeen[$url])) {
                $skipped++;
                continue;
            }
            if ($url) $urlSeen[$url] = true;

            // ── [PP-3] BARIS 118-120: BERSIHKAN FASILITAS ──────────
            // Memanggil cleanFasilitas() yang melakukan:
            // a) Hapus dimensi kamar (contoh: "3 x 4 meter")
            // b) Hapus karakter encoding rusak (â€™)
            // c) Normalisasi nama via $fasilitasMap
            // d) Hapus duplikat dalam satu baris
            $facilitiesClean = $this->cleanFasilitas($item['all_facilities_bs'] ?? '');

            // ── [PP-4] BARIS 123-126: NORMALISASI LISTRIK ──────────
            // "Termasuk listrik (implied)" → "Termasuk Listrik"
            // Jika kolom kosong, fallback dicek dari string fasilitas
            $electricity = $this->normalisasiListrik(
                $item['is_electricity_included'] ?? '',
                $facilitiesClean
            );

            // ── [PP-5] BARIS 129-131: NORMALISASI KETERSEDIAAN ─────
            // "Tersisa1 kamar" → "Tersisa 1 Kamar"
            // "Kamar penuh,lihat kos lainnya" → "Kamar Penuh"
            // null → "Tersedia"
            $availability = $this->normalisasiKetersediaan(
                $item['room_availability_bs'] ?? null
            );

            // ── [PP-6] BARIS 134: PARSING RATING → float ───────────
            // "Not found" → null
            // "4.8" → 4.8 (float)
            // Nilai di luar 1.0–5.0 dibuang → null
            $rating = $this->parseRating($item['rating'] ?? null);

            // ── [PP-7] BARIS 137: PARSING RATING COUNT → integer ───
            // "(5)" → 5
            // "Not found" → 0
            $ratingCount = $this->parseAngka($item['rating_count'] ?? null);

            // ── [PP-8] BARIS 140: PARSING TRANSACTION COUNT ─────────
            // "22 transaksi berhasil di kos ini" → 22
            // "Not found" → 0
            $transCount = $this->parseAngka($item['transaction_count'] ?? null);

            // ── [PP-9] BARIS 143: KALKULASI LUAS KAMAR (m²) ────────
            // Mengekstrak dan menghitung luas dari string dimensi
            // "3 x 4 meter" → 12.0
            // "2.5 x 3.5 meter" → 8.75
            $roomSizeM2 = $this->hitungLuas($item['room_size'] ?? null);

            // ── [PP-10] BARIS 146-149: BERSIHKAN TEKS ───────────────
            // Menghapus whitespace berlebih dari nama, lokasi, region
            // "  Kost  ABC  " → "Kost ABC"
            $roomName  = $this->bersihkanTeks($item['room_name'] ?? '');
            $ownerName = $this->bersihkanTeks($item['owner_name'] ?? '');
            $location  = $this->bersihkanTeks($item['location'] ?? '');
            $region    = trim($item['region'] ?? '');

            // ── [PP-11] BARIS 152: NORMALISASI TIPE KOS ─────────────
            // Memastikan hanya 3 nilai valid yang masuk:
            // "Kos Campur", "Kos Putra", atau "Kos Putri"
            // Nilai lain → default "Kos Campur"
            $tipeKos = $this->normalisasiTipe($item['tipe_kos'] ?? '');

            // ── [PP-13] BARIS 162: PARSING HARGA SEBELUM DISKON ─────
            $priceBeforeDiscount = $this->parseHarga(
                $item['price_before_discount'] ?? null
            );


            // ── BARIS 168: SIMPAN DATA YANG SUDAH BERSIH ────────────
            $processed[] = [
                'room_name'               => $roomName,
                'owner_name'              => $ownerName ?: null,
                'region'                  => $region,
                'location'                => $location ?: null,
                'room_size'               => $item['room_size'] ?? null,
                'room_size_m2'            => $roomSizeM2,
                'is_electricity_included' => $electricity,
                'all_facilities'          => $facilitiesClean,
                'room_availability'       => $availability,
                'deposit_amount'          => $item['deposit_amount'] ?? null,
                'price'                   => $price,
                'price_display'           => $item['price_display'] ?? ('Rp ' . number_format($price, 0, ',', '.')),
                'price_before_discount'   => $priceBeforeDiscount,
                'rating'                  => $rating,
                'rating_count'            => $ratingCount,
                'transaction_count'       => $transCount,
                'tipe_kos'                => $tipeKos,
                'url'                     => $url ?: null,
                'image_url'               => $item['image_url'] ?? null,
                'is_active'               => true,
                'created_at'              => now(),
                'updated_at'              => now(),
            ];
        }

        // ── BARIS 196: INSERT KE DATABASE (batch 100) ───────────
        foreach (array_chunk($processed, 100) as $chunk) {
            DB::table('kos')->insert($chunk);
        }

        $this->command->info('Selesai! Tersimpan : ' . count($processed) . ' baris');
        $this->command->warn('Dibuang (duplikat/invalid): ' . $skipped . ' baris');
    }

    // =============================================================
    // BARIS 204 KE BAWAH: FUNGSI-FUNGSI PREPROCESSING
    // =============================================================

    /**
     * [PP-1] Parsing harga string → integer
     * Dipanggil di baris 101
     */
    private function parseHarga(mixed $val): ?int
    {
        if (empty($val) || $val === 'Not found') return null;
        $clean = preg_replace('/[Rp\.\s]/', '', (string) $val);
        $clean = explode('-', $clean)[0];
        $clean = str_replace(',', '', trim($clean));
        return is_numeric($clean) ? (int) $clean : null;
    }

    /**
     * [PP-3] Bersihkan & normalisasi fasilitas
     * Dipanggil di baris 118
     */
    private function cleanFasilitas(?string $val): string
    {
        if (empty($val)) return '';

        $items  = array_map('trim', explode(';', $val));
        $result = [];
        $seen   = [];

        foreach ($items as $item) {
            // a) Skip dimensi kamar (angka x angka meter)
            if (preg_match('/^[\d\.]+\s*x\s*[\d\.]+/i', $item)) continue;

            // b) Skip karakter encoding rusak
            if (preg_match('/[\x80-\xFF]|â€/', $item)) continue;
            if (empty($item)) continue;

            // c) Normalisasi via kamus fasilitasMap
            $key        = strtolower(trim($item));
            $normalized = $this->fasilitasMap[$key] ?? ucwords(strtolower($item));

            // d) Hapus duplikat dalam satu baris
            $lowerNorm = strtolower($normalized);
            if (isset($seen[$lowerNorm])) continue;
            $seen[$lowerNorm] = true;

            $result[] = $normalized;
        }

        return implode('; ', $result);
    }

    /**
     * [PP-4] Normalisasi status listrik
     * Dipanggil di baris 123
     */
    private function normalisasiListrik(?string $val, string $facilities): string
    {
        $v = strtolower($val ?? '');
        if (str_contains($v, 'tidak'))   return 'Tidak Termasuk Listrik';
        if (str_contains($v, 'termasuk') || str_contains($v, 'implied'))
            return 'Termasuk Listrik';

        $f = strtolower($facilities);
        if (str_contains($f, 'tidak termasuk listrik')) return 'Tidak Termasuk Listrik';
        if (str_contains($f, 'termasuk listrik'))       return 'Termasuk Listrik';

        return 'Tidak diketahui';
    }

    /**
     * [PP-5] Normalisasi ketersediaan kamar
     * Dipanggil di baris 129
     */
    private function normalisasiKetersediaan(?string $val): string
    {
        if (empty($val)) return 'Tersedia';
        $v = strtolower($val);
        if (str_contains($v, 'penuh')) return 'Kamar Penuh';
        if (preg_match('/tersisa\s*(\d+)/i', $v, $m)) return "Tersisa {$m[1]} Kamar";
        return 'Tersedia';
    }

    /**
     * [PP-6] Parsing rating → float
     * Dipanggil di baris 134
     */
    private function parseRating(mixed $val): ?float
    {
        if (empty($val) || $val === 'Not found') return null;
        $v = (float) preg_replace('/[^\d\.]/', '', (string) $val);
        return ($v >= 1.0 && $v <= 5.0) ? round($v, 1) : null;
    }

    /**
     * [PP-7 & PP-8] Parsing angka dari berbagai format string
     * Dipanggil di baris 137 dan 140
     */
    private function parseAngka(mixed $val): int
    {
        if (empty($val) || $val === 'Not found') return 0;
        preg_match('/\d+/', (string) $val, $m);
        return isset($m[0]) ? (int) $m[0] : 0;
    }

    /**
     * [PP-9] Hitung luas kamar dari string → float m²
     * Dipanggil di baris 143
     */
    private function hitungLuas(?string $val): ?float
    {
        if (empty($val)) return null;
        if (preg_match('/([\d\.]+)\s*x\s*([\d\.]+)/i', $val, $m)) {
            $luas = (float) $m[1] * (float) $m[2];
            return $luas > 0 ? round($luas, 2) : null;
        }
        return null;
    }

    /**
     * [PP-10] Bersihkan teks dari whitespace berlebih
     * Dipanggil di baris 146-149
     */
    private function bersihkanTeks(string $val): string
    {
        return trim(preg_replace('/\s+/', ' ', $val));
    }

    /**
     * [PP-11] Normalisasi tipe kos ke nilai yang valid
     * Dipanggil di baris 152
     */
    private function normalisasiTipe(string $val): string
    {
        $valid = ['Kos Campur', 'Kos Putra', 'Kos Putri'];
        return in_array($val, $valid) ? $val : 'Kos Campur';
    }
}
