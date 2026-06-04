<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RekomendasiController extends Controller
{
    public function __construct(private RecommendationService $service) {}

    /**
     * Show recommendation form
     */
    public function form()
    {
        $regions    = Kos::active()->distinct()->orderBy('region')->pluck('region');
        $tipeOptions = ['Kos Campur', 'Kos Putra', 'Kos Putri'];
        $facilities  = Kos::allFacilityOptions();

        return view('rekomendasi.form', compact('regions', 'tipeOptions', 'facilities'));
    }

    /**
     * Process recommendation
     */
    public function hasil(Request $request)
    {
        $validated = $request->validate([
            'budget_max'            => 'required|integer|min:100000|max:50000000',
            'budget_min'            => 'nullable|integer|min:0',
            'region'                => 'nullable|string',
            'tipe_kos'              => 'nullable|string',
            'facilities'            => 'nullable|array',
            'facilities.*'          => 'string',
        ]);

        $constraints = [
            'budget_max'            => (int) $validated['budget_max'],
            'budget_min'            => isset($validated['budget_min']) ? (int) $validated['budget_min'] : 0,
            'region'                => $validated['region'] ?? null,
            'tipe_kos'              => $validated['tipe_kos'] ?? null,
            'facilities'            => $validated['facilities'] ?? [],
            'limit'                 => 20,
        ];

        $result = $this->service->recommend($constraints);
        $stats  = $this->service->getStats($result['results']);

        $regions    = Kos::active()->distinct()->orderBy('region')->pluck('region');
        $tipeOptions = ['Kos Campur', 'Kos Putra', 'Kos Putri'];
        $facilities  = Kos::allFacilityOptions();

        return view('rekomendasi.hasil', [
            'results'          => $result['results'],
            'total_filtered'   => $result['total_filtered'],
            'constraints_used' => $result['constraints_used'],
            'stats'            => $stats,
            'constraints'      => $constraints,
            'regions'          => $regions,
            'tipeOptions'      => $tipeOptions,
            'facilities'       => $facilities,
        ]);
    }

    /**
     * Detail kos (modal/page)
     */
    public function detail(Kos $kos)
    {
        return view('rekomendasi.detail', compact('kos'));
    }

    /**
     * API: Quick recommendation (AJAX)
     */
    public function api(Request $request)
    {
        $constraints = [
            'budget_max'  => (int) $request->input('budget_max', 3000000),
            'budget_min'  => (int) $request->input('budget_min', 0),
            'region'      => $request->input('region'),
            'tipe_kos'    => $request->input('tipe_kos'),
            'facilities'  => $request->input('facilities', []),
            'limit'       => 10,
        ];

        $result = $this->service->recommend($constraints);

        return response()->json([
            'success' => true,
            'total'   => $result['total_filtered'],
            'results' => $result['results']->map(fn($k) => [
                'id'           => $k->id,
                'room_name'    => $k->room_name,
                'region'       => $k->region,
                'price'        => $k->price,
                'price_display'=> $k->formatted_price,
                'tipe_kos'     => $k->tipe_kos,
                'match_score'  => $k->match_score,
                'image_url'    => $k->image_url, // Sesuaikan dengan nama kolom migration
            ]),
        ]);
    }
}