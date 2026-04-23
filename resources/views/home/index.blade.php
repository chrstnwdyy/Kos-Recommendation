@extends('layouts.app')

@section('title', 'KosFindr – Rekomendasi Kos Jabodetabek')

@push('styles')
<style>
    /* ── Hero ── */
    .hero-section {
        background: linear-gradient(135deg, #4F46E5 0%, #06B6D4 100%);
        padding: 5rem 0 6rem;
        position: relative; overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .hero-title { font-size: clamp(2rem, 5vw, 3.2rem); font-weight: 800; color: #fff; }
    .hero-sub   { font-size: 1.1rem; color: rgba(255,255,255,0.85); }
    .hero-blob  {
        width: 320px; height: 320px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%; position: absolute;
        top: -80px; right: -80px;
    }
    .hero-blob-2 {
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%; position: absolute;
        bottom: -60px; left: 10%;
    }

    /* ── Search Card ── */
    .quick-search-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(79,70,229,0.2);
        padding: 2rem;
        margin-top: -3rem;
        position: relative; z-index: 10;
    }

    /* ── Stats ── */
    .stat-card { text-align: center; padding: 1.5rem 1rem; }
    .stat-num  { font-size: 2rem; font-weight: 800; color: var(--primary); }
    .stat-lbl  { font-size: 0.85rem; color: var(--muted); font-weight: 600; }

    /* ── How it works ── */
    .step-icon {
        width: 56px; height: 56px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; margin-bottom: 1rem;
    }

    /* ── Region pill ── */
    .region-pill {
        background: #EEF2FF; color: var(--primary);
        border-radius: 50px; padding: 8px 18px;
        font-weight: 600; font-size: 0.875rem;
        display: inline-flex; align-items: center; gap: 6px;
        text-decoration: none;
        transition: background 0.2s, transform 0.1s;
    }
    .region-pill:hover { background: #E0E7FF; transform: translateY(-1px); color: var(--primary-dk); }
</style>
@endpush

@section('content')

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-blob"></div>
    <div class="hero-blob-2"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="chip mb-3" style="background:rgba(255,255,255,0.2);color:#fff;">
                    <i class="bi bi-stars"></i> Knowledge-Based &amp; Constraint-Based Method
                </div>
                <h1 class="hero-title mb-3">
                    Temukan Kos Ideal<br>di <span style="text-decoration:underline;text-decoration-color:rgba(255,255,255,0.5);text-underline-offset:6px;">Jabodetabek</span>
                </h1>
                <p class="hero-sub mb-4">
                    Sistem rekomendasi cerdas yang menyesuaikan kamar kos dengan <strong>budget, lokasi, tipe, dan fasilitas</strong> yang Anda butuhkan — tanpa perlu scroll ribuan pilihan.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('rekomendasi.form') }}" class="btn btn-light btn-lg fw-700 px-4 rounded-pill">
                        <i class="bi bi-search me-2"></i>Cari Kos Sekarang
                    </a>
                    <a href="#cara-kerja" class="btn btn-outline-light btn-lg px-4 rounded-pill">
                        <i class="bi bi-play-circle me-2"></i>Cara Kerja
                    </a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                <!-- Decorative illustration -->
                <div style="position:relative;">
                    <div style="width:280px;height:280px;background:rgba(255,255,255,0.15);border-radius:24px;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(10px);">
                        <div style="text-align:center;color:#fff;">
                            <i class="bi bi-house-heart-fill" style="font-size:5rem;"></i>
                            <div style="font-size:1.2rem;font-weight:700;margin-top:1rem;">{{ number_format($stats['total_kos']) }}+ Kos</div>
                            <div style="font-size:0.875rem;opacity:0.8;">di Jabodetabek</div>
                        </div>
                    </div>
                    <!-- Floating badge -->
                    <div style="position:absolute;top:-16px;right:-24px;background:#fff;border-radius:12px;padding:10px 16px;box-shadow:0 8px 24px rgba(0,0,0,0.15);">
                        <div style="font-size:0.75rem;color:var(--muted);font-weight:600;">Mulai dari</div>
                        <div style="font-size:1rem;color:var(--primary);font-weight:800;">Rp {{ number_format($stats['min_price'], 0, ',', '.') }}</div>
                    </div>
                    <div style="position:absolute;bottom:-16px;left:-24px;background:#fff;border-radius:12px;padding:10px 16px;box-shadow:0 8px 24px rgba(0,0,0,0.15);">
                        <div style="font-size:0.75rem;color:var(--muted);font-weight:600;">Wilayah</div>
                        <div style="font-size:1rem;color:var(--success);font-weight:800;">{{ $stats['total_region'] }} Kota</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Search Card -->
<section class="pb-5">
    <div class="container">
        <div class="quick-search-card">
            <h5 class="fw-700 mb-1">🔍 Cari Kos Cepat</h5>
            <p class="text-muted small mb-3">Isi preferensi utama Anda, lalu temukan kos yang cocok</p>
            <form action="{{ route('rekomendasi.hasil') }}" method="POST" id="quickSearchForm">
                @csrf
                <div class="row g-3">
                    <!-- Budget -->
                    <div class="col-md-3">
                        <label class="form-label fw-600 small">Budget Maksimum</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">Rp</span>
                            <input type="number" name="budget_max" class="form-control border-start-0 ps-0"
                                placeholder="2.000.000" min="100000" step="100000" required>
                        </div>
                    </div>
                    <!-- Region -->
                    <div class="col-md-3">
                        <label class="form-label fw-600 small">Wilayah</label>
                        <select name="region" class="form-select">
                            <option value="">Semua Wilayah</option>
                            @foreach($regions as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Tipe -->
                    <div class="col-md-3">
                        <label class="form-label fw-600 small">Tipe Kos</label>
                        <select name="tipe_kos" class="form-select">
                            <option value="semua">Semua Tipe</option>
                            @foreach($tipeOptions as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Submit -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="bi bi-search me-2"></i>Cari Kos
                        </button>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        Ingin filter lebih detail?
                        <a href="{{ route('rekomendasi.form') }}" class="text-primary fw-600">Gunakan form lengkap →</a>
                    </small>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-4">
    <div class="container">
        <div class="row g-3">
            @php
                $statItems = [
                    ['icon'=>'bi-house-check-fill','color'=>'#4F46E5','bg'=>'#EEF2FF','num'=>number_format($stats['total_kos']).' +','lbl'=>'Total Kamar Kos'],
                    ['icon'=>'bi-geo-alt-fill','color'=>'#06B6D4','bg'=>'#ECFEFF','num'=>$stats['total_region'],'lbl'=>'Kota / Wilayah'],
                    ['icon'=>'bi-cash-stack','color'=>'#10B981','bg'=>'#ECFDF5','num'=>'Rp '.number_format((int)($stats['avg_price']/1000)).'K','lbl'=>'Rata-rata Harga/bulan'],
                    ['icon'=>'bi-stars','color'=>'#F59E0B','bg'=>'#FFFBEB','num'=>'100%','lbl'=>'Berbasis Constraint-Based'],
                ];
            @endphp
            @foreach($statItems as $s)
            <div class="col-6 col-md-3">
                <div class="card-modern stat-card">
                    <div style="width:48px;height:48px;background:{{ $s['bg'] }};border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <i class="bi {{ $s['icon'] }}" style="font-size:1.4rem;color:{{ $s['color'] }};"></i>
                    </div>
                    <div class="stat-num" style="color:{{ $s['color'] }};">{{ $s['num'] }}</div>
                    <div class="stat-lbl">{{ $s['lbl'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- How it works -->
<section class="py-5" id="cara-kerja">
    <div class="container">
        <div class="text-center mb-5">
            <div class="chip mx-auto mb-3">Cara Kerja</div>
            <h2 class="fw-800 fs-2">Bagaimana Sistem Bekerja?</h2>
            <p class="text-muted">Rekomendasi presisi menggunakan algoritma Knowledge-Based &amp; Constraint-Based</p>
        </div>
        <div class="row g-4">
            @php $steps = [
                ['num'=>'01','icon'=>'bi-sliders','color'=>'#4F46E5','bg'=>'#EEF2FF','title'=>'Input Preferensi','desc'=>'Masukkan budget, wilayah, tipe kos, dan fasilitas yang Anda butuhkan sebagai constraint.'],
                ['num'=>'02','icon'=>'bi-funnel-fill','color'=>'#06B6D4','bg'=>'#ECFEFF','title'=>'Hard Constraint Filtering','desc'=>'Sistem eliminasi kamar kos yang tidak memenuhi batasan wajib: budget, lokasi, tipe kos.'],
                ['num'=>'03','icon'=>'bi-calculator','color'=>'#10B981','bg'=>'#ECFDF5','title'=>'Constraint-Based Matching','desc'=>'Hitung skor kesesuaian fasilitas: Score = (Fasilitas Cocok / Total Permintaan) × 100%.'],
                ['num'=>'04','icon'=>'bi-trophy-fill','color'=>'#F59E0B','bg'=>'#FFFBEB','title'=>'Ranking & Rekomendasi','desc'=>'Tampilkan kamar kos terurut dari skor tertinggi dengan detail transparansi matching.'],
            ]; @endphp
            @foreach($steps as $step)
            <div class="col-md-6 col-lg-3">
                <div class="card-modern p-4 h-100">
                    <div class="step-icon" style="background:{{ $step['bg'] }};">
                        <i class="bi {{ $step['icon'] }}" style="color:{{ $step['color'] }};"></i>
                    </div>
                    <div class="chip mb-2" style="background:{{ $step['bg'] }};color:{{ $step['color'] }};">Step {{ $step['num'] }}</div>
                    <h6 class="fw-700 mb-2">{{ $step['title'] }}</h6>
                    <p class="text-muted small mb-0">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Jelajahi Wilayah -->
<section class="py-5" style="background:#F1F5F9;">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-800 fs-3">Jelajahi per Wilayah</h2>
            <p class="text-muted">Klik wilayah untuk langsung mencari kos di area tersebut</p>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            @foreach($regions as $r)
            <a href="{{ route('rekomendasi.hasil') }}" onclick="event.preventDefault(); submitRegion('{{ $r }}')" class="region-pill">
                <i class="bi bi-geo-alt-fill"></i>{{ $r }}
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5">
    <div class="container">
        <div class="text-center" style="background:linear-gradient(135deg,#4F46E5,#06B6D4);border-radius:24px;padding:3rem 2rem;">
            <h2 class="fw-800 text-white mb-3">Siap Menemukan Kos Impian?</h2>
            <p class="text-white opacity-75 mb-4">Gunakan sistem rekomendasi kami untuk menemukan kos yang benar-benar sesuai kebutuhan Anda</p>
            <a href="{{ route('rekomendasi.form') }}" class="btn btn-light btn-lg fw-700 px-5 rounded-pill">
                <i class="bi bi-stars me-2"></i>Mulai Cari Kos
            </a>
        </div>
    </div>
</section>

<!-- Hidden form for region click -->
<form id="regionForm" action="{{ route('rekomendasi.hasil') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="budget_max" value="5000000">
    <input type="hidden" name="region" id="regionInput">
</form>

@endsection

@push('scripts')
<script>
function submitRegion(region) {
    document.getElementById('regionInput').value = region;
    document.getElementById('regionForm').submit();
}
</script>
@endpush