<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RekomendasiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('rekomendasi')->name('rekomendasi.')->group(function () {
    Route::get('/', [RekomendasiController::class, 'form'])->name('form');
    Route::post('/hasil', [RekomendasiController::class, 'hasil'])->name('hasil');
    Route::get('/detail/{kos}', [RekomendasiController::class, 'detail'])->name('detail');
});

// API endpoint for AJAX
Route::post('/api/rekomendasi', [RekomendasiController::class, 'api'])->name('api.rekomendasi');