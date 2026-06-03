<?php

namespace App\Services;

use App\Models\Kos;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function recommend(array $constraints): array
    {
        // ── STEP 1: Hard Constraint Filtering ──────────────────────────────
        $query = Kos::active();

        // Budget (wajib)
        $budgetMax = (int) ($constraints['budget_max'] ?? 5000000);
        $budgetMin = (int) ($constraints['budget_min'] ?? 0);
        $query->where('price', '<=', $budgetMax);
        if ($budgetMin > 0) {
            $query->where('price', '>=', $budgetMin);
        }

        // Region
        if (!empty($constraints['region'])) {
            $query->where('region', $constraints['region']);
        }

        // Ukuran Kamar (m²)
        if (!empty($constraints['room_size_min'])) {
            $query->where('room_size_m2', '>=', (float) $constraints['room_size_min']);
        }
        if (!empty($constraints['room_size_max'])) {
            $query->where('room_size_m2', '<=', (float) $constraints['room_size_max']);
        }

        $filtered = $query->get();
        $totalFiltered = $filtered->count();

        // ── STEP 2: Soft Constraint Scoring (Facility Matching) ─────────────
        $requestedFacilities = (array) ($constraints['facilities'] ?? []);

        $scored = $filtered->map(function (Kos $kos) use ($requestedFacilities) {
            $score = $this->calculateMatchScore($kos, $requestedFacilities);
            $kos->match_score     = $score['percentage'];
            $kos->matched_count   = $score['matched'];
            $kos->requested_count = $score['total'];
            $kos->matched_list    = $score['matched_list'];
            $kos->missing_list    = $score['missing_list'];
            return $kos;
        });

        // ── STEP 3: Ranking ──────────────────────────────────────────────────
        $limit = (int) ($constraints['limit'] ?? 20);

        $ranked = $scored
            ->sortBy(function ($kos) {
                return [$kos->match_score * -1, $kos->price];
            })
            ->values()
            ->take($limit);

        return [
            'results'          => $ranked,
            'total_filtered'   => $totalFiltered,
            'constraints_used' => $this->describeConstraints($constraints),
        ];
    }

    private function calculateMatchScore(Kos $kos, array $requestedFacilities): array
    {
        if (empty($requestedFacilities)) {
            return [
                'percentage'   => 100.0,
                'matched'      => 0,
                'total'        => 0,
                'matched_list' => [],
                'missing_list' => [],
            ];
        }

        $kosFacilities = $kos->facilities_array;
        $matched = [];
        $missing = [];

        foreach ($requestedFacilities as $requested) {
            $found = false;
            foreach ($kosFacilities as $kf) {
                if (stripos($kf, $requested) !== false || stripos($requested, $kf) !== false) {
                    $found = true;
                    break;
                }
            }
            if ($found) $matched[] = $requested;
            else        $missing[] = $requested;
        }

        $total        = count($requestedFacilities);
        $matchedCount = count($matched);
        $percentage   = $total > 0 ? round(($matchedCount / $total) * 100, 1) : 100.0;

        return [
            'percentage'   => $percentage,
            'matched'      => $matchedCount,
            'total'        => $total,
            'matched_list' => $matched,
            'missing_list' => $missing,
        ];
    }

    private function describeConstraints(array $constraints): array
    {
        $desc = [];

        $budgetMax = $constraints['budget_max'] ?? null;
        $budgetMin = $constraints['budget_min'] ?? null;
        if ($budgetMax) {
            $range = 'Maks Rp ' . number_format($budgetMax, 0, ',', '.');
            if ($budgetMin) {
                $range = 'Rp ' . number_format($budgetMin, 0, ',', '.') . ' – Rp ' . number_format($budgetMax, 0, ',', '.');
            }
            $desc[] = ['label' => 'Budget', 'value' => $range, 'icon' => 'bi-cash-stack'];
        }

        if (!empty($constraints['region'])) {
            $desc[] = ['label' => 'Wilayah', 'value' => $constraints['region'], 'icon' => 'bi-geo-alt'];
        }

        if (!empty($constraints['room_size_min']) || !empty($constraints['room_size_max'])) {
            $min = $constraints['room_size_min'] ?? '0';
            $max = $constraints['room_size_max'] ?? '∞';
            $desc[] = ['label' => 'Ukuran Kamar', 'value' => $min . ' m² – ' . $max . ' m²', 'icon' => 'bi-rulers'];
        }

        if (!empty($constraints['facilities'])) {
            $desc[] = ['label' => 'Fasilitas', 'value' => implode(', ', $constraints['facilities']), 'icon' => 'bi-check2-circle'];
        }

        return $desc;
    }

    public function getStats(Collection $results): array
    {
        if ($results->isEmpty()) return [];

        return [
            'avg_price'     => (int) $results->avg('price'),
            'min_price'     => (int) $results->min('price'),
            'max_price'     => (int) $results->max('price'),
            'avg_score'     => round($results->avg('match_score'), 1),
            'perfect_match' => $results->where('match_score', 100)->count(),
        ];
    }
}
