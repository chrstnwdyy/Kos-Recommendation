@extends('layouts.app')

@section('title', 'Cari Kos – KosFindr')

@push('styles')
<style>
    .form-hero {
        background: linear-gradient(135deg, #4F46E5 0%, #06B6D4 100%);
        padding: 3rem 0 5rem;
    }
    .form-card {
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(79,70,229,0.15);
        padding: 2.5rem;
        margin-top: -3rem;
        position: relative; z-index: 10;
    }
    .step-tab {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 20px; border-radius: 12px;
        cursor: pointer; font-weight: 600; font-size: 0.9rem;
        border: 2px solid transparent; background: #F1F5F9;
        color: var(--muted); transition: all 0.2s;
    }
    .step-tab.active {
        background: #EEF2FF; color: var(--primary);
        border-color: var(--primary);
    }
    .step-tab .step-num {
        width: 26px; height: 26px; border-radius: 50%;
        background: var(--border); color: var(--muted);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
        transition: all 0.2s;
    }
    .step-tab.active .step-num { background: var(--primary); color: #fff; }
    .step-tab.done .step-num  { background: var(--success); color: #fff; }
    .step-tab.done { color: var(--success); border-color: var(--success); background: #ECFDF5; }

    .step-panel { display: none; }
    .step-panel.active { display: block; }

    /* Budget slider */
    .budget-display {
        font-size: 2rem; font-weight: 800; color: var(--primary);
        text-align: center; margin: 0.5rem 0;
    }
    .budget-range-input { width: 100%; accent-color: var(--primary); height: 6px; }

    /* Tipe Kos Radio Cards */
    .tipe-card-wrap { display: flex; gap: 10px; flex-wrap: wrap; }
    .tipe-card {
        flex: 1; min-width: 110px;
        border: 2px solid var(--border);
        border-radius: 12px; padding: 14px 10px;
        text-align: center; cursor: pointer;
        transition: all 0.2s; background: #fff;
    }
    .tipe-card:hover  { border-color: var(--primary); background: #EEF2FF; }
    .tipe-card.active { border-color: var(--primary); background: #EEF2FF; }
    .tipe-card input  { display: none; }
    .tipe-card i      { font-size: 1.8rem; display: block; margin-bottom: 6px; }

    /* Summary panel */
    .summary-item {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 10px 0; border-bottom: 1px solid var(--border);
    }
    .summary-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

<section class="form-hero">
    <div class="container text-center">
        <div class="chip mx-auto mb-3" style="background:rgba(255,255,255,0.2);color:#fff;">
            <i class="bi bi-sliders"></i> Filter Preferensi
        </div>
        <h1 class="text-white fw-800 mb-2" style="font-size:2rem;">Temukan Kos yang Tepat</h1>
        <p class="text-white opacity-75">Isi preferensi Anda dan biarkan sistem merekomendasikan yang terbaik</p>
    </div>
</section>

<section class="pb-5">
    <div class="container" style="max-width: 860px;">
        <div class="form-card">

            <div class="d-flex gap-2 mb-4 flex-wrap" id="stepTabs">
                <div class="step-tab active" data-step="1" onclick="goStep(1)">
                    <div class="step-num">1</div> Budget & Wilayah
                </div>
                <div class="step-tab" data-step="2" onclick="goStep(2)">
                    <div class="step-num">2</div> Tipe Kos
                </div>
                <div class="step-tab" data-step="3" onclick="goStep(3)">
                    <div class="step-num">3</div> Fasilitas
                </div>
                <div class="step-tab" data-step="4" onclick="goStep(4)">
                    <div class="step-num">4</div> Konfirmasi
                </div>
            </div>

            <form action="{{ route('rekomendasi.hasil') }}" method="POST" id="mainForm">
                @csrf

                <div class="step-panel active" id="step1">
                    <h5 class="fw-700 mb-1">💰 Budget & Lokasi</h5>
                    <p class="text-muted small mb-4">Tentukan anggaran bulanan dan wilayah yang diinginkan</p>

                    <div class="mb-4">
                        <label class="fw-600 mb-2">Budget Maksimum per Bulan <span class="text-danger">*</span></label>
                        <div class="budget-display" id="budgetDisplay">Rp 2.000.000</div>
                        <input type="range" class="budget-range-input" id="budgetSlider"
                               min="300000" max="10000000" step="100000" value="2000000">
                        <input type="hidden" name="budget_max" id="budgetMaxInput" value="2000000">
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Rp 300.000</small>
                            <small class="text-muted">Rp 10.000.000</small>
                        </div>
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            @foreach([500000,1000000,1500000,2000000,3000000,5000000] as $preset)
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill budget-preset"
                                    data-val="{{ $preset }}">
                                Rp {{ number_format($preset/1000000,1,',','.')}}Jt
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-600 small mb-1">Budget Minimum (opsional)</label>
                        <div class="input-group" style="max-width:280px;">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="budget_min" class="form-control"
                                   placeholder="0 = tidak dibatasi" min="0" step="100000">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-600 mb-2">Wilayah / Kota</label>
                        <div class="row g-2">
                            <div class="col-6 col-md-4">
                                <label class="d-flex align-items-center gap-2 p-3 rounded-3 border cursor-pointer region-opt"
                                       style="cursor:pointer;" data-val="">
                                    <input type="radio" name="region" value="" checked class="d-none">
                                    <i class="bi bi-globe-asia-australia text-primary fs-5"></i>
                                    <span class="fw-600 small">Semua Wilayah</span>
                                </label>
                            </div>
                            @foreach($regions as $r)
                            <div class="col-6 col-md-4">
                                <label class="d-flex align-items-center gap-2 p-3 rounded-3 border cursor-pointer region-opt"
                                       style="cursor:pointer;" data-val="{{ $r }}">
                                    <input type="radio" name="region" value="{{ $r }}" class="d-none">
                                    <i class="bi bi-geo-alt text-primary fs-5"></i>
                                    <span class="fw-600 small">{{ $r }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary-custom px-4" onclick="goStep(2)">
                            Lanjut <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <div class="step-panel" id="step2">
                    <h5 class="fw-700 mb-1">🏠 Tipe Kos</h5>
                    <p class="text-muted small mb-4">Pilih tipe kos yang sesuai</p>

                    <div class="mb-4">
                        <label class="fw-600 mb-3">Tipe Kos</label>
                        <div class="tipe-card-wrap">
                            @php $tipeIcons = ['Kos Campur'=>'bi-people-fill','Kos Putra'=>'bi-gender-male','Kos Putri'=>'bi-gender-female']; @endphp
                            <label class="tipe-card active" id="tipe-semua">
                                <input type="radio" name="tipe_kos" value="semua" checked>
                                <i class="bi bi-house-fill text-primary"></i>
                                <div class="fw-600 small">Semua Tipe</div>
                                <div class="text-muted" style="font-size:0.7rem;">Campur/Putra/Putri</div>
                            </label>
                            @foreach($tipeOptions as $t)
                            <label class="tipe-card" id="tipe-{{ Str::slug($t) }}">
                                <input type="radio" name="tipe_kos" value="{{ $t }}">
                                <i class="bi {{ $tipeIcons[$t] ?? 'bi-house' }}" style="color:#06B6D4;"></i>
                                <div class="fw-600 small">{{ $t }}</div>
                                <div class="text-muted" style="font-size:0.7rem;">
                                    @if($t=='Kos Campur') Laki-laki & Perempuan
                                    @elseif($t=='Kos Putra') Khusus Laki-laki
                                    @else Khusus Perempuan @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goStep(1)">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-primary-custom px-4" onclick="goStep(3)">
                            Lanjut <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <div class="step-panel" id="step3">
                    <h5 class="fw-700 mb-1">✨ Fasilitas yang Diinginkan</h5>
                    <p class="text-muted small mb-3">Pilih fasilitas yang penting bagi Anda. Semakin banyak dipilih, skor matching lebih selektif.</p>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small">Fasilitas terpilih: <strong id="facCount" class="text-primary">0</strong></span>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" onclick="clearFacilities()">
                            <i class="bi bi-x me-1"></i>Hapus Semua
                        </button>
                    </div>

                    @php
                    $facGroups = [
                        'Kenyamanan Kamar' => ['AC'=>'AC','Kipas Angin'=>'Kipas Angin','Kasur'=>'Kasur','Lemari Baju'=>'Lemari','Meja'=>'Meja Belajar','Kursi'=>'Kursi','Cermin'=>'Cermin'],
                        'Kamar Mandi'      => ['K. Mandi Dalam'=>'KM Dalam','K. Mandi Luar'=>'KM Luar','Air panas'=>'Air Panas','Shower'=>'Shower','Bathtub'=>'Bathtub'],
                        'Elektronik'       => ['WiFi'=>'WiFi','TV'=>'TV','Kulkas'=>'Kulkas','Mesin Cuci'=>'Mesin Cuci','Rice Cooker'=>'Rice Cooker'],
                        'Dapur & Ruang'    => ['Dapur'=>'Dapur','Dapur Pribadi'=>'Dapur Pribadi','R. Makan'=>'R. Makan','R. Tamu'=>'R. Tamu','R. Santai'=>'R. Santai'],
                        'Keamanan'         => ['CCTV'=>'CCTV','Kartu Akses'=>'Kartu Akses','Penjaga Kos'=>'Penjaga Kos','Pengurus Kos'=>'Pengurus Kos'],
                        'Parkir & Fasilitas Luar' => ['Parkir Motor'=>'Parkir Motor','Parkir Mobil'=>'Parkir Mobil','Laundry'=>'Laundry','Mushola'=>'Mushola','Balcon'=>'Balkon','Taman'=>'Taman'],
                    ];
                    $facIcons = [
                        'AC'=>'bi-wind','Kipas Angin'=>'bi-fan','Kasur'=>'bi-moon-stars','Lemari Baju'=>'bi-archive',
                        'Meja'=>'bi-journal','Kursi'=>'bi-person-workspace','Cermin'=>'bi-circle',
                        'K. Mandi Dalam'=>'bi-droplet-fill','K. Mandi Luar'=>'bi-droplet','Air panas'=>'bi-thermometer-sun',
                        'Shower'=>'bi-droplet-half','Bathtub'=>'bi-water',
                        'WiFi'=>'bi-wifi','TV'=>'bi-tv','Kulkas'=>'bi-snow','Mesin Cuci'=>'bi-arrow-repeat','Rice Cooker'=>'bi-cup-hot',
                        'Dapur'=>'bi-fire','Dapur Pribadi'=>'bi-house-heart','R. Makan'=>'bi-people','R. Tamu'=>'bi-door-open','R. Santai'=>'bi-emoji-smile',
                        'CCTV'=>'bi-camera-video','Kartu Akses'=>'bi-credit-card-2-front','Penjaga Kos'=>'bi-shield-check','Pengurus Kos'=>'bi-person-check',
                        'Parkir Motor'=>'bi-bicycle','Parkir Mobil'=>'bi-car-front','Laundry'=>'bi-bag','Mushola'=>'bi-moon','Balcon'=>'bi-stars','Taman'=>'bi-tree',
                    ];
                    @endphp

                    @foreach($facGroups as $groupName => $groupItems)
                    <div class="mb-3">
                        <div class="fw-600 small text-muted mb-2 text-uppercase" style="letter-spacing:0.05em;">{{ $groupName }}</div>
                        <div class="fac-check-wrapper">
                            @foreach($groupItems as $val => $label)
                            <label class="fac-check-label">
                                <input type="checkbox" name="facilities[]" value="{{ $val }}" class="fac-cb">
                                <i class="bi {{ $facIcons[$val] ?? 'bi-check2' }}"></i>
                                {{ $label }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goStep(2)">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </button>
                        <button type="button" class="btn btn-primary-custom px-4" onclick="goStep(4)">
                            Lanjut <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                <div class="step-panel" id="step4">
                    <h5 class="fw-700 mb-1">✅ Konfirmasi Preferensi</h5>
                    <p class="text-muted small mb-4">Pastikan semua preferensi sudah sesuai sebelum mencari</p>

                    <div id="summaryPanel" class="mb-4 p-3 rounded-3" style="background:#F8FAFC;border:1px solid var(--border);">
                        </div>

                    <div class="p-3 rounded-3 mb-4" style="background:#EEF2FF;border:1px solid #C7D2FE;">
                        <div class="d-flex gap-3">
                            <i class="bi bi-info-circle-fill text-primary fs-5 flex-shrink-0 mt-1"></i>
                            <div>
                                <div class="fw-600 text-primary small">Tentang Algoritma</div>
                                <div class="text-muted small mt-1">
                                    Sistem akan melakukan <strong>Hard Constraint Filtering</strong> untuk mengeliminasi kos yang tidak memenuhi budget & lokasi,
                                    kemudian menghitung <strong>Skor Matching</strong> berdasarkan fasilitas yang Anda pilih menggunakan rumus:<br>
                                    <code>Score = (Fasilitas Cocok / Total Permintaan) × 100%</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goStep(3)">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-primary-custom px-5" id="submitBtn">
                            <i class="bi bi-stars me-2"></i>Dapatkan Rekomendasi
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
let currentStep = 1;

// ── Step navigation ────────────────────────────────────────────
function goStep(n) {
    // hide all
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');

    // update tabs
    document.querySelectorAll('.step-tab').forEach(t => {
        const s = parseInt(t.dataset.step);
        t.classList.remove('active', 'done');
        if (s === n) t.classList.add('active');
        else if (s < n) t.classList.add('done');
    });

    currentStep = n;
    if (n === 4) buildSummary();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Budget slider ──────────────────────────────────────────────
const slider = document.getElementById('budgetSlider');
const display = document.getElementById('budgetDisplay');
const hiddenInput = document.getElementById('budgetMaxInput');

function formatRp(val) {
    return 'Rp ' + parseInt(val).toLocaleString('id-ID');
}

slider.addEventListener('input', () => {
    display.textContent = formatRp(slider.value);
    hiddenInput.value = slider.value;
});

document.querySelectorAll('.budget-preset').forEach(btn => {
    btn.addEventListener('click', () => {
        slider.value = btn.dataset.val;
        display.textContent = formatRp(btn.dataset.val);
        hiddenInput.value = btn.dataset.val;
    });
});

// ── Region radio cards ─────────────────────────────────────────
document.querySelectorAll('.region-opt').forEach(label => {
    label.addEventListener('click', () => {
        document.querySelectorAll('.region-opt').forEach(l => {
            l.classList.remove('border-primary', 'bg-primary-subtle');
            l.style.background = '';
            l.style.borderColor = '';
        });
        label.style.borderColor = 'var(--primary)';
        label.style.background  = '#EEF2FF';
        label.querySelector('input').checked = true;
    });
});
// init
document.querySelector('.region-opt[data-val=""]').style.borderColor = 'var(--primary)';
document.querySelector('.region-opt[data-val=""]').style.background = '#EEF2FF';

// ── Tipe Kos cards ─────────────────────────────────────────────
document.querySelectorAll('.tipe-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.tipe-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        card.querySelector('input').checked = true;
    });
});

// ── Facility count ─────────────────────────────────────────────
function updateFacCount() {
    const count = document.querySelectorAll('.fac-cb:checked').length;
    document.getElementById('facCount').textContent = count;
}
document.querySelectorAll('.fac-cb').forEach(cb => cb.addEventListener('change', updateFacCount));

function clearFacilities() {
    document.querySelectorAll('.fac-cb').forEach(cb => {
        cb.checked = false;
        cb.closest('.fac-check-label').classList.remove('active');
    });
    updateFacCount();
}

// ── Build summary for step 4 ───────────────────────────────────
function buildSummary() {
    const budget   = formatRp(hiddenInput.value);
    const budgetMin = document.querySelector('[name=budget_min]').value;
    const region   = document.querySelector('[name=region]:checked')?.value || 'Semua Wilayah';
    const tipe     = document.querySelector('[name=tipe_kos]:checked')?.value || 'Semua Tipe';
    const facs     = [...document.querySelectorAll('.fac-cb:checked')].map(c => c.value);

    let html = '';
    const row = (icon, label, val) =>
        `<div class="summary-item">
            <i class="bi ${icon} text-primary fs-5 flex-shrink-0"></i>
            <div>
                <div class="text-muted" style="font-size:0.75rem;">${label}</div>
                <div class="fw-600 small">${val}</div>
            </div>
        </div>`;

    html += row('bi-cash-stack', 'Budget Maksimum', budget);
    if (budgetMin && budgetMin > 0) html += row('bi-cash', 'Budget Minimum', formatRp(budgetMin));
    html += row('bi-geo-alt-fill', 'Wilayah', region || 'Semua Wilayah');
    html += row('bi-house', 'Tipe Kos', tipe === 'semua' ? 'Semua Tipe' : tipe);

    if (facs.length > 0) {
        const pills = facs.map(f => `<span class="chip me-1 mb-1">${f}</span>`).join('');
        html += `<div class="summary-item"><i class="bi bi-check2-circle text-primary fs-5 flex-shrink-0"></i>
            <div><div class="text-muted" style="font-size:0.75rem;">Fasilitas (${facs.length})</div>
            <div class="mt-1">${pills}</div></div></div>`;
    } else {
        html += row('bi-check2-circle', 'Fasilitas', 'Tidak dipilih (semua ditampilkan)');
    }

    document.getElementById('summaryPanel').innerHTML = html;
}

// ── Submit loading ─────────────────────────────────────────────
document.getElementById('mainForm').addEventListener('submit', () => {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mencari...';
});
</script>
@endpush