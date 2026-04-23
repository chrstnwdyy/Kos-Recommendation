<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'total_kos'    => Kos::active()->count(),
            'total_region' => Kos::active()->distinct('region')->count('region'),
            'avg_price'    => (int) Kos::active()->avg('price'),
            'min_price'    => (int) Kos::active()->min('price'),
        ];

        $regions    = Kos::active()->distinct()->orderBy('region')->pluck('region');
        $tipeOptions = ['Kos Campur', 'Kos Putra', 'Kos Putri'];
        $facilities  = Kos::allFacilityOptions();

        return view('home.index', compact('stats', 'regions', 'tipeOptions', 'facilities'));
    }
}