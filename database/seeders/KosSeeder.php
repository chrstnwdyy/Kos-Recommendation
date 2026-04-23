<?php

namespace Database\Seeders;

use App\Models\Kos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KosSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding kos data...');
        DB::table('kos')->truncate();

        $jsonPath = database_path('data/kos_data.json');
        if (!file_exists($jsonPath)) {
            $this->command->error('Data file not found: ' . $jsonPath);
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $chunks = array_chunk($data, 100);

        $now = now();
        foreach ($chunks as $chunk) {
            $rows = array_map(function ($item) use ($now) {
                return array_merge($item, [
                    'is_active'  => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }, $chunk);
            DB::table('kos')->insert($rows);
        }

        $this->command->info('Seeded ' . count($data) . ' kos records.');
    }
}