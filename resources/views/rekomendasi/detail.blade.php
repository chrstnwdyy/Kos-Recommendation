@extends('layouts.app')

@section('title', $kos->room_name . ' – KosFindr')

@section('content')
<div class="container py-5" style="max-width:900px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rekomendasi.form') }}" class="text-decoration-none">Cari Kos</a></li>
            <li class="breadcrumb-item active text-muted">{{ Str::limit($kos->room_name, 40) }}</li>
        </ol>
    </nav>

    <div class="card-modern overflow-hidden">
        <!-- Image -->
        <img src="{{ $kos->image }}" alt="{{ $kos->room_name }}"
             style="width:100%;height:300px;object-fit:cover;"
             onerror="this.src='https://placehold.co/900x300/E2E8F0/94A3B8?text=No+Image'">

        <div class="p-4">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
                <div>
                    <h1 class="fw-800 mb-1" style="font-size:1.5rem;">{{ $kos->room_name }}</h1>
                    <div class="text-muted small">
                        <i class="bi bi-geo-alt me-1"></i>{{ $kos->region }}{{ $kos->location ? ', '.$kos->location : '' }}
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-800" style="font-size:1.6rem;color:var(--primary);">{{ $kos->formatted_price }}</div>
                    <div class="text-muted small">/bulan</div>
                </div>
            </div>

            <!-- Info grid -->
            <div class="row g-3 mb-4">
                @php
                $infos = [
                    ['icon'=>'bi-house','label'=>'Tipe','val'=>$kos->tipe_kos],
                    ['icon'=>'bi-rulers','label'=>'Ukuran Kamar','val'=>$kos->room_size ?? '-'],
                    ['icon'=>'bi-lightning','label'=>'Listrik','val'=>$kos->is_electricity_included ?? '-'],
                    ['icon'=>'bi-door-open','label'=>'Ketersediaan','val'=>$kos->room_availability ?? 'Tersedia'],
                ];
                if($kos->deposit_amount && $kos->deposit_amount !== 'Not found')
                    $infos[] = ['icon'=>'bi-cash','label'=>'Deposit','val'=>$kos->deposit_amount];
                if($kos->rating && $kos->rating !== 'Not found')
                    $infos[] = ['icon'=>'bi-star-fill','label'=>'Rating','val'=>$kos->rating . ($kos->rating_count ? ' ('.$kos->rating_count.' ulasan)' : '')];
                if($kos->owner_name)
                    $infos[] = ['icon'=>'bi-person','label'=>'Pemilik','val'=>$kos->owner_name];
                @endphp
                @foreach($infos as $info)
                <div class="col-6 col-md-4">
                    <div class="p-3 rounded-3" style="background:#F8FAFC;border:1px solid #E2E8F0;">
                        <div class="text-muted" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">{{ $info['label'] }}</div>
                        <div class="fw-700 mt-1"><i class="bi {{ $info['icon'] }} me-1 text-primary"></i>{{ $info['val'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Facilities -->
            <h5 class="fw-700 mb-3">Fasilitas Kamar</h5>
            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach($kos->facilities_array as $fac)
                    @if(!preg_match('/\d+\s*x\s*\d+/', $fac))
                    <span class="chip chip-gray">{{ $fac }}</span>
                    @endif
                @endforeach
            </div>

            <!-- CTA -->
            <div class="d-flex gap-3 flex-wrap">
                @if($kos->url)
                <a href="{{ $kos->url }}" target="_blank" class="btn btn-primary-custom px-4">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Lihat di Mamikos
                </a>
                @endif
                <a href="{{ route('rekomendasi.form') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left me-2"></i>Cari Kos Lain
                </a>
            </div>
        </div>
    </div>
</div>
@endsection