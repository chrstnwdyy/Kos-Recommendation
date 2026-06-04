<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Kos extends Model
{
    use HasFactory;

    protected $table = 'kos';

    protected $fillable = [
        'room_name', 'region', 'price', 'price_display',
        'all_facilities', 'tipe_kos', 'url', 'image_url', 'is_active'
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function getFacilitiesArrayAttribute(): array
    {
        if (empty($this->all_facilities)) return [];
        return array_map('trim', explode(';', $this->all_facilities));
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->price_display ?? 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    // Mengambil semua fasilitas unik yang ada di DB untuk ditampilkan di Form
    public static function allFacilityOptions(): array
    {
        $all = static::active()->pluck('all_facilities')->toArray();
        $unique = [];
        foreach ($all as $facString) {
            if (!$facString) continue;
            $items = array_map('trim', explode(';', $facString));
            foreach ($items as $item) {
                if (!empty($item)) $unique[$item] = $item;
            }
        }
        ksort($unique);
        return $unique;
    }
}