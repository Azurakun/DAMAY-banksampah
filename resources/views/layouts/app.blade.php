<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1a5c28">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="EcoBank">
    <title>@yield('title', 'EcoBank SMKN 2 Indramayu')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Sistem Aplikasi Bank Sampah & Tabungan digital SMKN 2 Indramayu yang ramah lingkungan dan terintegrasi.">
    <meta name="keywords" content="EcoBank, Bank Sampah, SMKN 2 Indramayu, Tabungan Sampah, PWA">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="/css/app.css?v={{ filemtime(public_path('css/app.css')) }}">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="https://img.icons8.com/color/192/000000/eco-energy.png">

    @yield('styles')
</head>
<body>
<div id="app" class="app-container">

    <!-- ── TOP HEADER ── -->
    <header class="top-header">
        <div class="header-content">
            <a href="/" class="brand" style="text-decoration:none;">
                <div class="brand-icon">
                    <i class="bi bi-recycle"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-name">EcoBank</span>
                    <span class="brand-sub">SMKN 2 Indramayu</span>
                </div>
            </a>

            @auth
                <!-- Desktop Nav Links -->
                <nav class="desktop-nav">
                    @if(Auth::user()->role === 'siswa')
                        <a href="{{ route('siswa.dashboard') }}"   class="{{ Route::is('siswa.dashboard')   ? 'active' : '' }}"><i class="bi bi-house-door"></i> Beranda</a>
                        <a href="{{ route('siswa.history') }}"     class="{{ Route::is('siswa.history')     ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Riwayat</a>
                        <a href="{{ route('siswa.leaderboard') }}" class="{{ Route::is('siswa.leaderboard') ? 'active' : '' }}"><i class="bi bi-trophy"></i> Peringkat</a>
                        <a href="{{ route('siswa.withdraw') }}"    class="{{ Route::is('siswa.withdraw')    ? 'active' : '' }}"><i class="bi bi-cash-coin"></i> Tarik Dana</a>
                        <a href="{{ route('siswa.profile') }}"     class="{{ Route::is('siswa.profile')     ? 'active' : '' }}"><i class="bi bi-person"></i> Profil</a>
                    @elseif(Auth::user()->role === 'operator')
                        <a href="{{ route('operator.dashboard') }}"         class="{{ Route::is('operator.dashboard')         ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dasbor</a>
                        <a href="{{ route('operator.students.register') }}" class="{{ Route::is('operator.students.register') ? 'active' : '' }}"><i class="bi bi-person-plus"></i> Registrasi</a>
                        <a href="{{ route('operator.history') }}"           class="{{ Route::is('operator.history')           ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Riwayat</a>
                        <a href="{{ route('operator.profile') }}"           class="{{ Route::is('operator.profile')           ? 'active' : '' }}"><i class="bi bi-person"></i> Profil</a>
                    @elseif(Auth::user()->role === 'walikelas')
                        <a href="{{ route('walikelas.dashboard') }}"   class="{{ Route::is('walikelas.dashboard') ? 'active' : '' }}"><i class="bi bi-mortarboard"></i> Portal Wali Kelas</a>
                        <a href="{{ route('walikelas.pendaftar') }}"   class="{{ Route::is('walikelas.pendaftar') ? 'active' : '' }}"><i class="bi bi-person-check-fill"></i> Pendaftar Baru</a>
                    @elseif(Auth::user()->role === 'manajer')
                        <a href="{{ route('manajer.dashboard') }}"      class="{{ Route::is('manajer.dashboard') ? 'active' : '' }}"><i class="bi bi-shield-check"></i> Portal Manajer</a>
                        <a href="{{ route('manajer.users') }}"          class="{{ Route::is('manajer.users') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> Daftar Pengguna</a>
                        <a href="{{ route('manajer.profile') }}"        class="{{ Route::is('manajer.profile') ? 'active' : '' }}"><i class="bi bi-person"></i> Profil</a>
                    @endif
                </nav>

                <!-- User chip + logout (desktop) -->
                <div class="user-menu-desktop">
                    <div class="user-avatar-chip">
                        <div class="user-avatar-circle">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div style="display:flex;flex-direction:column;line-height:1.1;">
                            <span class="user-name-label">{{ explode(' ', Auth::user()->name)[0] }}</span>
                            <span class="user-role-label">{{ ucfirst(Auth::user()->role) }}</span>
                        </div>
                    </div>
                    <form id="logout-form-desktop" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                    <button type="button" class="logout-btn-desktop" onclick="document.getElementById('logout-form-desktop').submit()">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </button>
                </div>
            @endauth
        </div>
    </header>

    <!-- ── MAIN CONTENT ── -->
    <main class="main-content">

        {{-- Flash: Success --}}
        @if(session('success'))
            <div class="alert alert-success animate-fade-in" id="flash-success">
                <span class="alert-icon"><i class="bi bi-check-circle-fill"></i></span>
                <span class="alert-message">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Flash: Error --}}
        @if(session('error'))
            <div class="alert alert-danger animate-fade-in" id="flash-error">
                <span class="alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
                <span class="alert-message">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- ── BOTTOM NAVIGATION (Mobile/PWA) ── -->
    @auth
        <nav class="bottom-nav">
            @if(Auth::user()->role === 'siswa')
                <a href="{{ route('siswa.dashboard') }}"   class="nav-item {{ Route::is('siswa.dashboard')   ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-house-door{{ Route::is('siswa.dashboard')   ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Beranda</span>
                    </div>
                </a>
                <a href="{{ route('siswa.history') }}"     class="nav-item {{ Route::is('siswa.history')     ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="nav-label">Riwayat</span>
                    </div>
                </a>
                <a href="{{ route('siswa.leaderboard') }}" class="nav-item {{ Route::is('siswa.leaderboard') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-trophy{{ Route::is('siswa.leaderboard') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Peringkat</span>
                    </div>
                </a>
                <a href="{{ route('siswa.withdraw') }}"    class="nav-item {{ Route::is('siswa.withdraw')    ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-cash-coin"></i></span>
                        <span class="nav-label">Tarik</span>
                    </div>
                </a>
                <a href="{{ route('siswa.profile') }}"     class="nav-item {{ Route::is('siswa.profile')     ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-person{{ Route::is('siswa.profile') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Profil</span>
                    </div>
                </a>
            @elseif(Auth::user()->role === 'operator')
                <a href="{{ route('operator.dashboard') }}"         class="nav-item {{ Route::is('operator.dashboard')         ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                        <span class="nav-label">Dasbor</span>
                    </div>
                </a>
                <a href="{{ route('operator.students.register') }}" class="nav-item {{ Route::is('operator.students.register') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-person-plus{{ Route::is('operator.students.register') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Daftar</span>
                    </div>
                </a>
                <a href="{{ route('operator.history') }}"           class="nav-item {{ Route::is('operator.history')           ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="nav-label">Riwayat</span>
                    </div>
                </a>
                <a href="{{ route('operator.profile') }}"           class="nav-item {{ Route::is('operator.profile')           ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-person{{ Route::is('operator.profile') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Profil</span>
                    </div>
                </a>
            @elseif(Auth::user()->role === 'manajer')
                <a href="{{ route('manajer.dashboard') }}"      class="nav-item {{ Route::is('manajer.dashboard') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                        <span class="nav-label">Dasbor</span>
                    </div>
                </a>
                <a href="{{ route('manajer.users') }}"          class="nav-item {{ Route::is('manajer.users') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-people{{ Route::is('manajer.users') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Pengguna</span>
                    </div>
                </a>
                <a href="{{ route('manajer.profile') }}"        class="nav-item {{ Route::is('manajer.profile') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-person{{ Route::is('manajer.profile') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Profil</span>
                    </div>
                </a>
            @endif
        </nav>
    @endauth

</div><!-- #app -->

<!-- PWA Service Worker -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('[SW] Registered:', reg.scope))
                .catch(err => console.warn('[SW] Failed:', err));
        });
    }

    // Auto-dismiss flash alerts after 5s
    document.addEventListener('DOMContentLoaded', () => {
        ['flash-success', 'flash-error'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                setTimeout(() => {
                    el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-8px)';
                    setTimeout(() => el.remove(), 400);
                }, 5000);
            }
        });
    });
</script>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

@yield('scripts')
</body>
</html>
