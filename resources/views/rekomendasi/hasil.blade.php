@extends('layouts.app')

@section('title', 'Hasil Rekomendasi – KosFindr')

@push('styles')
<style>
    .hasil-hero {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        padding: 2.5rem 0;
    }
    /* ── Kos Card ── */
    .kos-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        transition: box-shadow 0.2s, transform 0.2s;
        height: 100%;
    }
    .kos-card:hover {
        box-shadow: 0 8px 32px rgba(79,70,229,0.14);
        transform: translateY(-3px);
    }
    .kos-card-img {
        width: 100%; height: 200px;
        object-fit: cover;
        background: #E2E8F0;
    }
    .kos-card-body { padding: 1rem 1.1rem; }

    /* ── Score Ring ── */
    .score-ring {
        width: 52px; height: 52px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; font-weight: 800;
        flex-shrink: 0;
        border: 3px solid;
    }
    .score-ring.perfect { border-color:#10B981; color:#065F46; background:#D1FAE5; }
    .score-ring.high    { border-color:#4F46E5; color:#3730A3; background:#EEF2FF; }
    .score-ring.medium  { border-color:#F59E0B; color:#92400E; background:#FEF3C7; }
    .score-ring.low     { border-color:#EF4444; color:#991B1B; background:#FEE2E2; }

    /* ── Rank badge ── */
    .rank-badge {
        position: absolute; top: 10px; left: 10px;
        background: rgba(0,0,0,0.65); backdrop-filter: blur(4px);
        color: #fff; font-size: 0.72rem; font-weight: 700;
        padding: 4px 10px; border-radius: 20px;
    }
    .rank-1 { background: linear-gradient(135deg,#F59E0B,#EF4444); }
    .rank-2 { background: linear-gradient(135deg,#94A3B8,#64748B); }
    .rank-3 { background: linear-gradient(135deg,#92400E,#B45309); }

    /* ── Tipe badge ── */
    .tipe-badge {
        position: absolute; top: 10px; right: 10px;
        font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;
    }
    .tipe-campur { background:#DBEAFE; color:#1D4ED8; }
    .tipe-putra  { background:#DCFCE7; color:#166534; }
    .tipe-putri  { background:#FCE7F3; color:#9D174D; }

    /* ── Filter sidebar ── */
    .filter-sidebar {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.25rem;
        position: sticky; top: 80px;
    }

    /* ── Progress bar ── */
    .match-bar { height: 6px; border-radius: 3px; background: #E2E8F0; }
    .match-bar-fill { height: 100%; border-radius: 3px; transition: width 0.6s; }

    /* ── Sort select ── */
    .sort-select { border: 1.5px solid var(--border); border-radius: 8px; padding: 6px 12px; font-size: 0.85rem; font-weight: 600; }

    /* ── No result ── */
    .no-result { text-align:center; padding: 4rem 2rem; }

    /* ── Modal ── */
    .modal-fac-list { display:flex; flex-wrap:wrap; gap:6px; }
</style>
@endpush

@section('content')

<!-- Hero bar -->
<section class="hasil-hero">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="chip mb-2" style="background:rgba(255,255,255,0.15);color:#fff;">
                    <i class="bi bi-stars"></i> Hasil Rekomendasi
                </div>
                <h1 class="text-white fw-800 mb-1" style="font-size:1.6rem;">
                    Ditemukan <span style="color:#06B6D4;">{{ $total_filtered }}</span> kos
                    @if($total_filtered > 0), menampilkan <span style="color:#06B6D4;">{{ $results->count() }}</span> terbaik
                    @endif
                </h1>
                <!-- Constraint chips -->
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($constraints_used as $c)
                    <span class="chip" style="background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);">
                        <i class="bi {{ $c['icon'] }}"></i>{{ $c['value'] }}
                    </span>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('rekomendasi.form') }}" class="btn btn-outline-light rounded-pill px-4">
                <i class="bi bi-sliders me-2"></i>Ubah Filter
            </a>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">

        @if($results->isEmpty())
        <!-- ── No Result ── -->
        <div class="no-result card-modern">
            <i class="bi bi-house-x" style="font-size:4rem;color:var(--border);"></i>
            <h4 class="fw-700 mt-3">Tidak Ada Kos yang Sesuai</h4>
            <p class="text-muted">Coba perluas kriteria pencarian Anda, misalnya naikkan budget atau hapus beberapa fasilitas.</p>
            <a href="{{ route('rekomendasi.form') }}" class="btn btn-primary-custom mt-2">
                <i class="bi bi-arrow-left me-2"></i>Kembali & Ubah Filter
            </a>
        </div>
        @else

        <div class="row g-4">
            <!-- ── Sidebar ── -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="filter-sidebar">
                    <h6 class="fw-700 mb-3"><i class="bi bi-funnel me-2 text-primary"></i>Filter Cepat</h6>

                    <!-- Ulangi form singkat -->
                    <form action="{{ route('rekomendasi.hasil') }}" method="POST">
                        @csrf
                        <input type="hidden" name="budget_max" value="{{ $constraints['budget_max'] }}">
                        <input type="hidden" name="budget_min" value="{{ $constraints['budget_min'] }}">

                        <div class="mb-3">
                            <label class="small fw-600 mb-1">Wilayah</label>
                            <select name="region" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                @foreach($regions as $r)
                                <option value="{{ $r }}" {{ ($constraints['region'] ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-600 mb-1">Ukuran Kamar Min (m²)</label>
                            <input type="number" name="room_size_min" class="form-control form-control-sm"
                                   placeholder="misal: 6" min="0" step="0.5"
                                   value="{{ $constraints['room_size_min'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-600 mb-1">Ukuran Kamar Max (m²)</label>
                            <input type="number" name="room_size_max" class="form-control form-control-sm"
                                   placeholder="misal: 20" min="0" step="0.5"
                                   value="{{ $constraints['room_size_max'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-600 mb-1">Fasilitas</label>
                            <div style="max-height:180px;overflow-y:auto;">
                                @foreach($facilities as $val => $label)
                                <div class="form-check form-check-sm mb-1">
                                    <input class="form-check-input" type="checkbox" name="facilities[]"
                                           value="{{ $val }}" id="sf_{{ $loop->index }}"
                                           {{ in_array($val, $constraints['facilities'] ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="sf_{{ $loop->index }}">{{ $label }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom btn-sm w-100">
                            <i class="bi bi-search me-1"></i>Terapkan
                        </button>
                    </form>

                    @if(!empty($stats))
                    <hr>
                    <h6 class="fw-700 mb-2 small text-muted text-uppercase">Statistik Hasil</h6>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Harga terendah</span>
                            <span class="fw-600">Rp {{ number_format($stats['min_price'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Harga tertinggi</span>
                            <span class="fw-600">Rp {{ number_format($stats['max_price'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Rata-rata harga</span>
                            <span class="fw-600">Rp {{ number_format($stats['avg_price'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Rata-rata skor</span>
                            <span class="fw-600 text-primary">{{ $stats['avg_score'] }}%</span>
                        </div>
                        @if($stats['perfect_match'] > 0)
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Cocok 100%</span>
                            <span class="fw-600 text-success">{{ $stats['perfect_match'] }} kos</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- ── Main Results ── -->
            <div class="col-lg-9">
                <!-- Sort + Count bar -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <small class="text-muted">Menampilkan <strong>{{ $results->count() }}</strong> dari <strong>{{ $total_filtered }}</strong> kos yang memenuhi syarat</small>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">Urut:</small>
                        <select class="sort-select" id="sortSelect">
                            <option value="score">Skor Terbaik</option>
                            <option value="price_asc">Harga Terendah</option>
                            <option value="price_desc">Harga Tertinggi</option>
                        </select>
                    </div>
                </div>

                <!-- Cards grid -->
                <div class="row g-3" id="kosGrid">
                    @foreach($results as $i => $kos)
                    <div class="col-md-6 col-xl-4 kos-col"
                         data-score="{{ $kos->match_score }}"
                         data-price="{{ $kos->price }}">
                        <div class="kos-card h-100">
                            <!-- Image -->
                            <div class="position-relative">
                                <img src="{{ $kos->image }}" alt="{{ $kos->room_name }}"
                                     class="kos-card-img"
                                     onerror="this.src='https://placehold.co/360x200/E2E8F0/94A3B8?text=No+Image'">
                                <!-- Rank -->
                                <div class="rank-badge {{ $i < 3 ? 'rank-'.($i+1) : '' }}">
                                    @if($i === 0) 🥇 #1
                                    @elseif($i === 1) 🥈 #2
                                    @elseif($i === 2) 🥉 #3
                                    @else #{{ $i + 1 }}
                                    @endif
                                </div>
                                <!-- Tipe badge -->
                                @php
                                    $tipeClass = match($kos->tipe_kos) {
                                        'Kos Putra' => 'tipe-putra',
                                        'Kos Putri' => 'tipe-putri',
                                        default     => 'tipe-campur',
                                    };
                                @endphp
                                <div class="tipe-badge {{ $tipeClass }}">{{ $kos->tipe_kos }}</div>
                            </div>

                            <!-- Body -->
                            <div class="kos-card-body">
                                <!-- Name & region -->
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <h6 class="fw-700 mb-0 lh-sm" style="font-size:0.875rem;line-height:1.3;">
                                        {{ Str::limit($kos->room_name, 50) }}
                                    </h6>
                                    <!-- Score ring -->
                                    @php
                                        $sc = $kos->match_score;
                                        $ringClass = $sc >= 100 ? 'perfect' : ($sc >= 70 ? 'high' : ($sc >= 40 ? 'medium' : 'low'));
                                    @endphp
                                    <div class="score-ring {{ $ringClass }}">{{ $sc }}%</div>
                                </div>

                                <div class="d-flex align-items-center gap-1 text-muted mb-2" style="font-size:0.78rem;">
                                    <i class="bi bi-geo-alt"></i>
                                    <span>{{ $kos->region }}{{ $kos->location ? ', '.$kos->location : '' }}</span>
                                </div>

                                <!-- Price -->
                                <div class="fw-800 mb-2" style="font-size:1.1rem;color:var(--primary);">
                                    {{ $kos->formatted_price }}
                                    <span class="text-muted fw-400" style="font-size:0.75rem;">/bulan</span>
                                </div>

                                <!-- Match progress -->
                                @if(!empty($constraints['facilities']))
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span style="font-size:0.72rem;color:var(--muted);font-weight:600;">Kesesuaian Fasilitas</span>
                                        <span style="font-size:0.72rem;font-weight:700;color:{{ $sc>=70?'var(--primary)':($sc>=40?'var(--warning)':'var(--danger)') }};">
                                            {{ $kos->matched_count }}/{{ $kos->requested_count }}
                                        </span>
                                    </div>
                                    <div class="match-bar">
                                        <div class="match-bar-fill"
                                             style="width:{{ $sc }}%;background:{{ $sc>=100?'var(--success)':($sc>=70?'var(--primary)':($sc>=40?'var(--warning)':'var(--danger)')) }};">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Top facilities -->
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    @foreach(array_slice($kos->facilities_array, 0, 4) as $fac)
                                        @if(!preg_match('/\d+\s*x\s*\d+/', $fac) && !in_array($fac, ['Termasuk listrik','Tidak termasuk listrik']))
                                        <span class="chip chip-gray" style="font-size:0.65rem;padding:2px 7px;">{{ $fac }}</span>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Rating -->
                                @if($kos->rating && $kos->rating !== 'Not found')
                                <div class="d-flex align-items-center gap-1 mb-2" style="font-size:0.78rem;">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <span class="fw-600">{{ $kos->rating }}</span>
                                    @if($kos->rating_count)
                                    <span class="text-muted">({{ $kos->rating_count }} ulasan)</span>
                                    @endif
                                </div>
                                @endif

                                <!-- CTA -->
                                <div class="d-flex gap-2">
                                    <button type="button"
                                            class="btn btn-primary-custom btn-sm flex-fill"
                                            onclick="showDetail({{ $kos->id }})">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </button>
                                    @if($kos->url)
                                    <a href="{{ $kos->url }}" target="_blank"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- ── Detail Modal ── -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0" id="modalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Memuat detail...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Sort ────────────────────────────────────────────────────────
document.getElementById('sortSelect').addEventListener('change', function() {
    const grid = document.getElementById('kosGrid');
    const cols  = [...grid.querySelectorAll('.kos-col')];

    cols.sort((a, b) => {
        if (this.value === 'score')
            return parseFloat(b.dataset.score) - parseFloat(a.dataset.score);
        if (this.value === 'price_asc')
            return parseInt(a.dataset.price) - parseInt(b.dataset.price);
        if (this.value === 'price_desc')
            return parseInt(b.dataset.price) - parseInt(a.dataset.price);
    });

    grid.innerHTML = '';
    cols.forEach(c => grid.appendChild(c));
});

// ── Detail Modal ────────────────────────────────────────────────
const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));

// Inline detail data passed from blade
const kosData = {
    @foreach($results as $kos)
    {{ $kos->id }}: {
        room_name:    "{{ addslashes($kos->room_name) }}",
        owner_name:   "{{ addslashes($kos->owner_name ?? '-') }}",
        region:       "{{ $kos->region }}",
        location:     "{{ addslashes($kos->location ?? '') }}",
        tipe_kos:     "{{ $kos->tipe_kos }}",
        price:        "{{ $kos->formatted_price }}",
        price_raw:    {{ $kos->price }},
        rating:       "{{ $kos->rating ?? '-' }}",
        rating_count: {{ $kos->rating_count ?? 0 }},
        room_size:    "{{ addslashes($kos->room_size ?? '-') }}",
        room_size_m2: "{{ $kos->room_size_m2 ?? '' }}",
        availability: "{{ addslashes($kos->room_availability ?? 'Tersedia') }}",
        deposit:      "{{ addslashes($kos->deposit_amount ?? '-') }}",
        facilities:   {!! json_encode($kos->facilities_array) !!},
        matched:      {!! json_encode($kos->matched_list ?? []) !!},
        missing:      {!! json_encode($kos->missing_list ?? []) !!},
        score:        {{ $kos->match_score ?? 100 }},
        matched_cnt:  {{ $kos->matched_count ?? 0 }},
        total_cnt:    {{ $kos->requested_count ?? 0 }},
        image:        "{{ addslashes($kos->image) }}",
        url:          "{{ addslashes($kos->url ?? '') }}",
        electricity:  "{{ addslashes($kos->is_electricity_included ?? '') }}",
    },
    @endforeach
};

function showDetail(id) {
    const k = kosData[id];
    if (!k) return;

    const sc        = k.score;
    const ringCls   = sc >= 100 ? 'perfect' : sc >= 70 ? 'high' : sc >= 40 ? 'medium' : 'low';
    const ringColor = sc >= 100 ? '#10B981' : sc >= 70 ? '#4F46E5' : sc >= 40 ? '#F59E0B' : '#EF4444';

    // Facilities – matched (green), missing (red), others (gray)
    const matched = new Set(k.matched);
    const missing = new Set(k.missing);
    const realFacs = k.facilities.filter(f => !/\d+\s*x\s*\d+/.test(f) && f !== 'Termasuk listrik' && f !== 'Tidak termasuk listrik');

    const facHtml = realFacs.map(f => {
        if (matched.has(f)) return `<span class="fac-pill fac-yes"><i class="bi bi-check"></i>${f}</span>`;
        if (missing.has(f)) return `<span class="fac-pill fac-no"><i class="bi bi-x"></i>${f}</span>`;
        return `<span class="fac-pill" style="background:#F1F5F9;color:#64748B;border-color:#E2E8F0;">${f}</span>`;
    }).join('');

    const missingHtml = k.missing.length > 0
        ? k.missing.map(f => `<span class="fac-pill fac-no"><i class="bi bi-x"></i>${f}</span>`).join('')
        : '<span class="text-success small"><i class="bi bi-check-circle me-1"></i>Semua fasilitas terpenuhi!</span>';

    document.getElementById('modalBody').innerHTML = `
        <div class="row g-0">
            <div class="col-md-5">
                <img src="${k.image}" alt="${k.room_name}"
                     style="width:100%;height:260px;object-fit:cover;"
                     onerror="this.src='https://placehold.co/360x260/E2E8F0/94A3B8?text=No+Image'">
            </div>
            <div class="col-md-7 p-4">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <h5 class="fw-800 lh-sm mb-0">${k.room_name}</h5>
                    <div class="score-ring ${ringCls}" style="flex-shrink:0;">${sc}%</div>
                </div>
                <div class="text-muted small mb-3">
                    <i class="bi bi-geo-alt me-1"></i>${k.region}${k.location ? ', ' + k.location : ''}
                </div>

                <!-- Price -->
                <div class="fw-800 mb-3" style="font-size:1.4rem;color:var(--primary);">
                    ${k.price} <span class="text-muted fw-400" style="font-size:0.85rem;">/bulan</span>
                </div>

                <!-- Info grid -->
                <div class="row g-2 mb-3">
                    ${infoCell('bi-house','Tipe', k.tipe_kos)}
                    ${infoCell('bi-rulers','Ukuran', k.room_size)}
                    ${k.room_size_m2 ? infoCell('bi-rulers','Luas Kamar', k.room_size_m2 + ' m²') : ''}
                    ${infoCell('bi-door-open','Ketersediaan', k.availability || 'Tersedia')}
                    ${k.deposit && k.deposit !== 'Not found' && k.deposit !== '-' ? infoCell('bi-cash','Deposit', k.deposit) : ''}
                    ${k.rating !== '-' ? infoCell('bi-star-fill','Rating', k.rating + (k.rating_count ? ' (' + k.rating_count + ' ulasan)' : '')) : ''}
                </div>
            </div>
        </div>

        <div class="p-4 pt-0">
            <!-- Matching score explanation -->
            ${k.total_cnt > 0 ? `
            <div class="p-3 rounded-3 mb-3" style="background:#EEF2FF;border:1px solid #C7D2FE;">
                <div class="fw-700 small text-primary mb-1"><i class="bi bi-calculator me-1"></i>Analisis Constraint-Based Matching</div>
                <div class="text-muted small mb-2">
                    Skor = (${k.matched_cnt} fasilitas cocok / ${k.total_cnt} total permintaan) × 100% = <strong style="color:${ringColor}">${sc}%</strong>
                </div>
                <div class="match-bar"><div class="match-bar-fill" style="width:${sc}%;background:${ringColor};"></div></div>
            </div>
            ` : ''}

            <!-- All Facilities -->
            <h6 class="fw-700 mb-2">Semua Fasilitas</h6>
            <div class="modal-fac-list mb-3">${facHtml || '<span class="text-muted small">Data fasilitas tidak tersedia</span>'}</div>

            <!-- Missing facilities -->
            ${k.total_cnt > 0 ? `
            <h6 class="fw-700 mb-2">Fasilitas yang Tidak Tersedia</h6>
            <div class="modal-fac-list mb-3">${missingHtml}</div>
            ` : ''}

            ${k.url ? `<a href="${k.url}" target="_blank" class="btn btn-primary-custom w-100">
                <i class="bi bi-box-arrow-up-right me-2"></i>Lihat di Mamikos
            </a>` : ''}
        </div>
    `;

    detailModal.show();
}

function infoCell(icon, label, val) {
    return `<div class="col-6">
        <div class="p-2 rounded-2" style="background:#F8FAFC;border:1px solid #E2E8F0;">
            <div class="text-muted" style="font-size:0.68rem;font-weight:600;">${label}</div>
            <div class="fw-600 small"><i class="bi ${icon} me-1 text-primary"></i>${val}</div>
        </div>
    </div>`;
}
</script>
@endpush