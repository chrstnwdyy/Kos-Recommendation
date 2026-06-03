<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RekomendasiController extends Controller
{
    public function __construct(private RecommendationService $service) {}

    public function form()
    {
        $regions   = Kos::active()->distinct()->orderBy('region')->pluck('region');
        $facilities = Kos::allFacilityOptions();

        return view('rekomendasi.form', compact('regions', 'facilities'));
    }

    public function hasil(Request $request)
    {
        $validated = $request->validate([
            'budget_max'    => 'required|integer|min:100000|max:50000000',
            'budget_min'    => 'nullable|integer|min:0',
            'region'        => 'nullable|string',
            'facilities'    => 'nullable|array',
            'facilities.*'  => 'string',
            'room_size_min' => 'nullable|numeric|min:0',
            'room_size_max' => 'nullable|numeric|min:0',
        ]);

        $constraints = [
            'budget_max'    => (int) $validated['budget_max'],
            'budget_min'    => isset($validated['budget_min']) ? (int) $validated['budget_min'] : 0,
            'region'        => $validated['region'] ?? null,
            'facilities'    => $validated['facilities'] ?? [],
            'room_size_min' => $validated['room_size_min'] ?? null,
            'room_size_max' => $validated['room_size_max'] ?? null,
            'limit'         => 20,
        ];

        $result = $this->service->recommend($constraints);
        $stats  = $this->service->getStats($result['results']);

        $regions   = Kos::active()->distinct()->orderBy('region')->pluck('region');
        $facilities = Kos::allFacilityOptions();

        return view('rekomendasi.hasil', [
            'results'          => $result['results'],
            'total_filtered'   => $result['total_filtered'],
            'constraints_used' => $result['constraints_used'],
            'stats'            => $stats,
            'constraints'      => $constraints,
            'regions'          => $regions,
            'facilities'       => $facilities,
        ]);
    }

    public function detail(Kos $kos)
    {
        return view('rekomendasi.detail', compact('kos'));
    }
}
