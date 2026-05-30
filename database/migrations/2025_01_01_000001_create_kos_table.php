<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kos', function (Blueprint $table) {
            $table->id();

            // ── Identitas ──────────────────────────────
            $table->string('room_name');
            $table->string('owner_name')->nullable();
            $table->string('region');
            $table->string('location')->nullable();

            // ── Ukuran kamar ───────────────────────────
            $table->string('room_size')->nullable();           // string asli: "3 x 4 meter"

            // ── Harga ──────────────────────────────────
            $table->integer('price');                          // [PP-1] integer bersih
            $table->string('price_display');                   // string asli untuk tampilan
            $table->integer('price_before_discount')->nullable(); // [PP-13]
            

            // ── Fasilitas & listrik ────────────────────
            $table->text('all_facilities')->nullable();        // [PP-3] sudah dinormalisasi
            $table->string('is_electricity_included')->nullable(); // [PP-4]

            // ── Ketersediaan ───────────────────────────
            $table->string('room_availability')->nullable();   // [PP-5]
            $table->string('deposit_amount')->nullable();

            // ── Rating & transaksi ─────────────────────
            $table->decimal('rating', 3, 1)->nullable();       // [PP-6]
            $table->integer('rating_count')->default(0);       // [PP-7]
            $table->integer('transaction_count')->default(0);  // [PP-8]

            // ── Tipe & skor ────────────────────────────
            $table->enum('tipe_kos', ['Kos Campur', 'Kos Putra', 'Kos Putri'])->default('Kos Campur'); // [PP-11]

            // ── URL & gambar ───────────────────────────
            $table->string('url')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kos');
    }
};
