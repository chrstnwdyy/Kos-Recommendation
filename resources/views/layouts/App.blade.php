<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KosFindr') – Sistem Rekomendasi Kos Jabodetabek</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:    #4F46E5;
            --primary-dk: #3730A3;
            --accent:     #06B6D4;
            --success:    #10B981;
            --warning:    #F59E0B;
            --danger:     #EF4444;
            --bg:         #F8FAFC;
            --card-bg:    #FFFFFF;
            --text:       #0F172A;
            --muted:      #64748B;
            --border:     #E2E8F0;
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg); color: var(--text); }

        /* ── Navbar ── */
        .navbar-custom {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            box-shadow: 0 1px 0 var(--border);
        }
        .navbar-brand-text { font-weight: 800; color: var(--primary); font-size: 1.3rem; }
        .navbar-brand-text span { color: var(--accent); }

        /* ── Cards ── */
        .card-modern {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .card-modern:hover {
            box-shadow: 0 8px 30px rgba(79,70,229,0.12);
            transform: translateY(-2px);
        }

        /* ── Buttons ── */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.6rem 1.4rem;
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-primary-custom:hover { opacity: 0.92; transform: translateY(-1px); color: #fff; }

        /* ── Score Badge ── */
        .score-badge {
            font-size: 0.78rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .score-perfect  { background: #D1FAE5; color: #065F46; }
        .score-high     { background: #DBEAFE; color: #1D4ED8; }
        .score-medium   { background: #FEF3C7; color: #92400E; }
        .score-low      { background: #FEE2E2; color: #991B1B; }

        /* ── Chip / Tag ── */
        .chip {
            display: inline-flex; align-items: center; gap: 4px;
            background: #EEF2FF; color: var(--primary);
            font-size: 0.72rem; font-weight: 600;
            padding: 3px 10px; border-radius: 20px;
        }
        .chip-gray { background: #F1F5F9; color: var(--muted); }

        /* ── Facility Pill ── */
        .fac-pill {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 0.7rem; font-weight: 600; padding: 3px 9px;
            border-radius: 20px; border: 1px solid;
        }
        .fac-yes { background:#D1FAE5; color:#065F46; border-color:#A7F3D0; }
        .fac-no  { background:#FEE2E2; color:#991B1B; border-color:#FCA5A5; }

        /* ── Range slider ── */
        input[type=range] { accent-color: var(--primary); }

        /* ── Checkbox cards ── */
        .fac-check-wrapper { display: flex; flex-wrap: wrap; gap: 8px; }
        .fac-check-label {
            cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            background: #F1F5F9; color: var(--muted);
            font-size: 0.8rem; font-weight: 600;
            padding: 6px 12px; border-radius: 8px;
            border: 2px solid transparent;
            transition: all 0.15s;
            user-select: none;
        }
        .fac-check-label input { display: none; }
        .fac-check-label:hover { background: #EEF2FF; color: var(--primary); }
        .fac-check-label.active {
            background: #EEF2FF; color: var(--primary);
            border-color: var(--primary);
        }

        /* ── Progress bar ── */
        .match-progress { height: 6px; border-radius: 3px; }

        /* ── Toast ── */
        .toast-custom {
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            z-index: 9999; min-width: 280px;
        }

        /* ── Footer ── */
        footer { border-top: 1px solid var(--border); }

        @media (max-width: 768px) {
            .card-kos-img { height: 180px !important; }
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#4F46E5,#06B6D4);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-house-heart-fill text-white" style="font-size:18px;"></i>
            </div>
            <span class="navbar-brand-text">Kos<span>Findr</span></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link fw-600 {{ request()->routeIs('home') ? 'text-primary fw-bold' : '' }}" href="{{ route('home') }}">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-600 {{ request()->routeIs('rekomendasi.*') ? 'text-primary fw-bold' : '' }}" href="{{ route('rekomendasi.form') }}">
                        <i class="bi bi-search me-1"></i>Cari Kos
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-primary-custom btn-sm" href="{{ route('rekomendasi.form') }}">
                        <i class="bi bi-stars me-1"></i>Mulai Sekarang
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main content -->
<main>
    @yield('content')
</main>

<!-- Footer -->
<footer class="py-4 mt-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="navbar-brand-text" style="font-size:1.1rem;">Kos<span style="color:var(--accent);">Findr</span></span>
                </div>
                <small class="text-muted">Sistem Rekomendasi Kamar Kos Jabodetabek menggunakan Knowledge-Based &amp; Constraint-Based Method.</small>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <small class="text-muted">
                    Dataset: <a href="https://www.kaggle.com/datasets/dendykurniariagman/mamikos-jabodetabek-boarding-room-listings" target="_blank" class="text-decoration-none">Mamikos Jabodetabek</a>
                    &bull; Skripsi Informatika &bull; Universitas Sanata Dharma 2025
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Facility checkbox toggle
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.fac-check-label').forEach(label => {
        const cb = label.querySelector('input[type=checkbox]');
        const update = () => label.classList.toggle('active', cb.checked);
        cb.addEventListener('change', update);
        update();
    });
});
</script>

@stack('scripts')
</body>
</html>