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
            
            // Atribut Display Minimal
            $table->string('room_name'); // Wajib ada untuk UI
            
            // 4 Atribut Utama Filtering
            $table->string('region');
            $table->integer('price');
            $table->string('price_display');
            $table->text('all_facilities')->nullable();
            $table->enum('tipe_kos', ['Kos Campur', 'Kos Putra', 'Kos Putri'])->default('Kos Campur');
            
            // URL
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