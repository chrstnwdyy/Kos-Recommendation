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
            $table->string('room_name');
            $table->string('owner_name')->nullable();
            $table->string('region');
            $table->string('location')->nullable();
            $table->string('room_size')->nullable();
            $table->string('is_electricity_included')->nullable();
            $table->text('all_facilities')->nullable();
            $table->string('room_availability')->nullable();
            $table->string('deposit_amount')->nullable();
            $table->integer('price'); // stored as integer (rupiah)
            $table->string('price_display'); // original string
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('rating_count')->nullable();
            $table->integer('transaction_count')->nullable();
            $table->enum('tipe_kos', ['Kos Campur', 'Kos Putra', 'Kos Putri'])->default('Kos Campur');
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