<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#123526">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="EcoBank">
    <title>@yield('title', 'EcoBank SMKN 2 Indramayu')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Sistem Aplikasi Bank Sampah & Tabungan digital SMKN 2 Indramayu yang ramah lingkungan dan terintegrasi.">
    <meta name="keywords" content="EcoBank, Bank Sampah, SMKN 2 Indramayu, Tabungan Sampah, PWA">

    <!-- Fonts (also imported in app.css; link tag here speeds first paint) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,800;0,9..144,900;1,9..144,500;1,9..144,600&family=Hanken+Grotesk:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600;700&display=swap" rel="stylesheet">

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

    @auth
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" onclick="toggleMobileSidebar()"></div>

        <!-- ── SIDEBAR NAVIGATION ── -->
        <aside class="sidebar-nav">
            <div class="sidebar-brand">
                <a href="/" class="brand" style="text-decoration:none;">
                    <div class="brand-icon">
                        <i class="bi bi-recycle"></i>
                    </div>
                    <div class="brand-text">
                        <span class="brand-name">EcoBank</span>
                        <span class="brand-sub">SMKN 2 Indramayu</span>
                    </div>
                </a>
            </div>
            
            <div class="sidebar-menu">
                @if(Auth::user()->role === 'siswa')
                    <div class="menu-section">HOME</div>
                    <a href="{{ route('siswa.dashboard') }}" class="menu-link {{ Route::is('siswa.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door"></i> Dasbor
                    </a>
                    
                    <div class="menu-section">LAYANAN</div>
                    <a href="{{ route('siswa.withdraw') }}" class="menu-link {{ Route::is('siswa.withdraw') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin"></i> Tarik Dana
                    </a>
                    <a href="{{ route('siswa.leaderboard') }}" class="menu-link {{ Route::is('siswa.leaderboard') ? 'active' : '' }}">
                        <i class="bi bi-trophy"></i> Peringkat
                    </a>
                    <a href="{{ route('siswa.history') }}" class="menu-link {{ Route::is('siswa.history') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Riwayat
                    </a>
                    
                    <div class="menu-section">LAINNYA</div>
                    <a href="{{ route('siswa.profile') }}" class="menu-link {{ Route::is('siswa.profile') ? 'active' : '' }}">
                        <i class="bi bi-person"></i> Profil
                    </a>
                    <a href="#" class="menu-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                @elseif(Auth::user()->role === 'operator')
                    <div class="menu-section">HOME</div>
                    <a href="{{ route('operator.dashboard') }}" class="menu-link {{ Route::is('operator.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dasbor
                    </a>
                    
                    <div class="menu-section">KELOLA NASABAH</div>
                    <a href="{{ route('operator.students.register') }}" class="menu-link {{ Route::is('operator.students.register') ? 'active' : '' }}">
                        <i class="bi bi-person-plus"></i> Registrasi
                    </a>
                    <a href="{{ route('operator.history') }}" class="menu-link {{ Route::is('operator.history') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Riwayat
                    </a>
                    
                    <div class="menu-section">LAINNYA</div>
                    <a href="{{ route('operator.profile') }}" class="menu-link {{ Route::is('operator.profile') ? 'active' : '' }}">
                        <i class="bi bi-person"></i> Profil
                    </a>
                    <a href="#" class="menu-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                @elseif(Auth::user()->role === 'walikelas')
                    <div class="menu-section">HOME</div>
                    <a href="{{ route('walikelas.dashboard') }}" class="menu-link {{ Route::is('walikelas.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-mortarboard"></i> Portal Wali Kelas
                    </a>
                    
                    <div class="menu-section">PERSUBTAN</div>
                    <a href="{{ route('walikelas.pendaftar') }}" class="menu-link {{ Route::is('walikelas.pendaftar') ? 'active' : '' }}">
                        <i class="bi bi-person-check-fill"></i> Pendaftar Baru
                    </a>
                    
                    <div class="menu-section">LAINNYA</div>
                    <a href="#" class="menu-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                @elseif(Auth::user()->role === 'manajer')
                    <div class="menu-section">HOME</div>
                    <a href="{{ route('manajer.dashboard') }}" class="menu-link {{ Route::is('manajer.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i> Portal Manajer
                    </a>
                    
                    <div class="menu-section">KELOLA</div>
                    <a href="{{ route('manajer.classrooms') }}" class="menu-link {{ Route::is('manajer.classrooms') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> Kelola Kelas
                    </a>
                    <a href="{{ route('manajer.users') }}" class="menu-link {{ Route::is('manajer.users') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Daftar Pengguna
                    </a>
                    
                    <div class="menu-section">LAINNYA</div>
                    <a href="{{ route('manajer.profile') }}" class="menu-link {{ Route::is('manajer.profile') ? 'active' : '' }}">
                        <i class="bi bi-person"></i> Profil
                    </a>
                    <a href="#" class="menu-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                @endif
            </div>
        </aside>
    @endauth

    <!-- ── MAIN CONTENT AREA ── -->
    <div class="app-main-area" style="width: 100%;">
        @auth
            <!-- Top Toolbar -->
            <div class="top-toolbar">
                <!-- Mobile Brand logo and text -->
                <div class="mobile-brand-toolbar">
                    <a href="/" class="brand-minimal-mobile" style="text-decoration:none; display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-recycle" style="color:var(--primary); font-size:20px;"></i>
                        <span style="font-family:var(--font-display); font-weight:800; color:var(--primary); font-size:16px;">EcoBank</span>
                    </a>
                </div>

                <button type="button" class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
                    <i class="bi bi-list"></i>
                </button>

                
                <div class="toolbar-right" style="margin-left: auto; position: relative;">
                    <div class="user-chip-minimal" style="display:flex; align-items:center; cursor:pointer;" onclick="toggleProfileDropdown(event)">
                        <div class="user-avatar-circle" style="background:var(--accent); color:white; font-weight:700; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; margin-right:8px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span style="font-size:13px; font-weight:700; color:var(--on-background); margin-right:6px;">{{ explode(' ', Auth::user()->name)[0] }}</span>
                        <i class="bi bi-chevron-down" style="font-size:10px; color:var(--on-surface-variant); opacity:0.8;"></i>
                    </div>

                    <!-- Dropdown Box -->
                    <div id="profileDropdown" class="profile-dropdown-card" style="display:none; position: absolute; top: 48px; right: 0; width: 260px; background: var(--surface); border: 1px solid var(--outline-variant); border-radius: var(--r-md); box-shadow: var(--shadow-lg); z-index: 1000; padding: var(--s-16) var(--s-20);">
                        <div style="display:flex; align-items:center; gap:var(--s-12); margin-bottom:var(--s-16);">
                            <div style="background:var(--primary); color:white; font-weight:800; width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div style="line-height:1.2; min-width: 0; flex-grow: 1;">
                                <div style="font-size:14px; font-weight:800; color:var(--on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</div>
                                <div style="font-size:11px; font-weight:700; color:var(--accent); text-transform:uppercase; margin-top:2px;">
                                    @if(Auth::user()->role === 'siswa')
                                        Siswa · {{ Auth::user()->class }}
                                    @elseif(Auth::user()->role === 'walikelas')
                                        Wali Kelas
                                    @elseif(Auth::user()->role === 'operator')
                                        Operator
                                    @elseif(Auth::user()->role === 'manajer')
                                        Manajer
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div style="font-size:12px; color:var(--on-surface-variant); margin-bottom:var(--s-16); word-break: break-all; border-top: 1px solid var(--outline-variant); padding-top:var(--s-12); opacity: 0.9;">
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                                <i class="bi bi-envelope" style="color:var(--primary);"></i>
                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ Auth::user()->email }}">{{ Auth::user()->email }}</span>
                            </div>
                            @if(Auth::user()->role === 'siswa')
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <i class="bi bi-credit-card" style="color:var(--primary);"></i>
                                    <span>NISN: {{ Auth::user()->nisn }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div style="border-top:1px dashed var(--outline); padding-top:var(--s-12); display:flex; flex-direction:column; gap:var(--s-8);">
                            <a href="{{ route(Auth::user()->role . '.profile') }}" class="dropdown-link" style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:var(--primary); text-decoration:none; padding: 4px 0; transition: color var(--dur-fast) var(--ease);">
                                <i class="bi bi-person-gear"></i> Edit Profil
                            </a>
                            <a href="#" class="dropdown-link" style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:700; color:var(--danger); text-decoration:none; padding: 4px 0; transition: color var(--dur-fast) var(--ease);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endauth

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
    </div>

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
            @elseif(Auth::user()->role === 'walikelas')
                <a href="{{ route('walikelas.dashboard') }}"    class="nav-item {{ Route::is('walikelas.dashboard') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-mortarboard{{ Route::is('walikelas.dashboard') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Dasbor</span>
                    </div>
                </a>
                <a href="{{ route('walikelas.pendaftar') }}"    class="nav-item {{ Route::is('walikelas.pendaftar') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-person-check{{ Route::is('walikelas.pendaftar') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Pendaftar</span>
                    </div>
                </a>
                <a href="{{ route('walikelas.profile') }}"      class="nav-item {{ Route::is('walikelas.profile') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-person{{ Route::is('walikelas.profile') ? '-fill' : '' }}"></i></span>
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
                <a href="{{ route('manajer.classrooms') }}"     class="nav-item {{ Route::is('manajer.classrooms') ? 'active' : '' }}">
                    <div class="nav-pill">
                        <span class="nav-icon"><i class="bi bi-building{{ Route::is('manajer.classrooms') ? '-fill' : '' }}"></i></span>
                        <span class="nav-label">Kelas</span>
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

    // Toggle Mobile Sidebar
    function toggleMobileSidebar() {
        document.body.classList.toggle('sidebar-open');
    }

    // Toggle Profile Dropdown
    function toggleProfileDropdown(event) {
        event.stopPropagation();
        const dd = document.getElementById('profileDropdown');
        if (dd) {
            dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        }
    }

    // Close dropdown on click outside
    window.addEventListener('click', function(e) {
        const dd = document.getElementById('profileDropdown');
        const trigger = document.querySelector('.user-chip-minimal');
        if (dd && dd.style.display === 'block' && !dd.contains(e.target) && !trigger.contains(e.target)) {
            dd.style.display = 'none';
        }
    });
</script>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

@yield('scripts')
</body>
</html>
