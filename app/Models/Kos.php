<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Kos extends Model
{
    protected $table = 'kos';

    protected $fillable = [
        'room_name', 'owner_name', 'region', 'location', 'room_size',
        'is_electricity_included', 'all_facilities', 'room_availability',
        'deposit_amount', 'price', 'price_display', 'rating', 'rating_count',
        'transaction_count', 'tipe_kos', 'url', 'image_url', 'is_active',
    ];

    protected $casts = [
        'price'           => 'integer',
        'rating'          => 'decimal:1',
        'rating_count'    => 'integer',
        'transaction_count' => 'integer',
        'is_active'       => 'boolean',
    ];

    /**
     * Parse facilities string into array
     */
    public function getFacilitiesArrayAttribute(): array
    {
        if (empty($this->all_facilities)) return [];
        return array_map('trim', explode(';', $this->all_facilities));
    }

    /**
     * Check if kos has a specific facility
     */
    public function hasFacility(string $facility): bool
    {
        return in_array($facility, $this->facilities_array);
    }

    /**
     * Scope: only active kos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format price to IDR display
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get thumbnail image (fallback if none)
     */
    public function getImageAttribute(): string
    {
        return $this->image_url ?? 'https://placehold.co/360x480/e2e8f0/94a3b8?text=No+Image';
    }

    /**
     * All supported facilities list (for filter UI)
     */
    public static function allFacilityOptions(): array
    {
        return [
            'AC'               => 'AC',
            'WiFi'             => 'WiFi',
            'K. Mandi Dalam'   => 'Kamar Mandi Dalam',
            'K. Mandi Luar'    => 'Kamar Mandi Luar',
            'Kasur'            => 'Kasur',
            'Lemari Baju'      => 'Lemari',
            'Meja'             => 'Meja Belajar',
            'Kursi'            => 'Kursi',
            'TV'               => 'TV',
            'Kulkas'           => 'Kulkas',
            'Mesin Cuci'       => 'Mesin Cuci',
            'Dapur'            => 'Dapur',
            'Parkir Motor'     => 'Parkir Motor',
            'Parkir Mobil'     => 'Parkir Mobil',
            'CCTV'             => 'CCTV',
            'Laundry'          => 'Laundry',
            'Mushola'          => 'Mushola',
            'Balcon'           => 'Balkon',
            'Air panas'        => 'Air Panas',
            'Kipas Angin'      => 'Kipas Angin',
        ];
    }
}