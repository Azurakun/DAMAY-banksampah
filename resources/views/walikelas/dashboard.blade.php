@extends('layouts.app')
@section('title', 'Portal Wali Kelas — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    {{-- Header --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="border-color:var(--teal);">
            <i class="bi bi-mortarboard" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Portal Wali Kelas</div>
            <div class="greeting-meta">
                <i class="bi bi-person-badge" style="margin-right:3px;"></i>{{ $teacher->name }}
                &nbsp;·&nbsp;
                <strong style="color:var(--primary);">{{ $className }}</strong>
            </div>
        </div>
        <div style="margin-left:auto;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </div>

    {{-- ===== FILTER PANEL ===== --}}
    <form method="GET" action="{{ route('walikelas.dashboard') }}" id="filter-form">
        <div class="card" style="padding:var(--s-16);margin-bottom:var(--s-20);">
            <div style="font-size:12.5px;font-weight:700;color:var(--on-surface-variant);text-transform:uppercase;letter-spacing:.05em;margin-bottom:var(--s-12);">
                <i class="bi bi-funnel" style="margin-right:4px;color:var(--primary);"></i> Filter Data Kontribusi
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s-12);">
                {{-- Filter: Kelas --}}
                <div>
                    <label style="font-size:11px;font-weight:600;color:var(--on-surface-variant);display:block;margin-bottom:4px;">Kelas</label>
                    <select id="filter-kelas" name="kelas" class="form-control" style="font-size:13px;padding:8px 10px;"
                            onchange="document.getElementById('filter-angkatan').value=''; this.form.submit();">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($allClasses as $kls)
                            <option value="{{ $kls }}" {{ $selectedClass === $kls && !$selectedAngkatan ? 'selected' : '' }}>
                                {{ $kls }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter: Angkatan --}}
                <div>
                    <label style="font-size:11px;font-weight:600;color:var(--on-surface-variant);display:block;margin-bottom:4px;">Angkatan</label>
                    <select id="filter-angkatan" name="angkatan" class="form-control" style="font-size:13px;padding:8px 10px;"
                            onchange="document.getElementById('filter-kelas').value=''; this.form.submit();">
                        <option value="">-- Semua Angkatan --</option>
                        @foreach($allAngkatan as $ang)
                            <option value="{{ $ang }}" {{ $selectedAngkatan === $ang ? 'selected' : '' }}>
                                {{ $ang }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Active filter badge --}}
            @if($selectedAngkatan || ($selectedClass && $selectedClass !== auth()->user()->class))
                <div style="margin-top:var(--s-10);display:flex;align-items:center;gap:var(--s-8);">
                    <span style="font-size:11.5px;color:var(--on-surface-variant);">Menampilkan:</span>
                    <span class="badge badge-teal" style="font-size:11.5px;">{{ $className }}</span>
                    <a href="{{ route('walikelas.dashboard') }}" style="font-size:11px;color:var(--primary);text-decoration:none;font-weight:600;">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            @endif
        </div>
    </form>

    {{-- Class Stats --}}
    <div class="operator-stats-grid">
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-teal"><i class="bi bi-recycle"></i></div>
            <div class="stat-item-label">Total Sampah Kelas</div>
            <div class="stat-item-value">{{ number_format($classTotalWeight, 1, ',', '.') }} <span style="font-size:14px;">kg</span></div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-green"><i class="bi bi-wallet2"></i></div>
            <div class="stat-item-label">Total Tabungan</div>
            <div class="stat-item-value" style="font-size:15px;">Rp {{ number_format($classTotalBalance, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-amber"><i class="bi bi-people"></i></div>
            <div class="stat-item-label">Jumlah Siswa</div>
            <div class="stat-item-value">{{ $students->count() }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-blue"><i class="bi bi-calculator"></i></div>
            <div class="stat-item-label">Rata-rata Saldo</div>
            <div class="stat-item-value" style="font-size:15px;">
                Rp {{ number_format($students->count() > 0 ? ($classTotalBalance / $students->count()) : 0, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Student Leaderboard for Class --}}
    <div class="card">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-bar-chart-line" style="color:var(--primary);"></i>
                Peringkat Kontribusi — {{ $className }}
            </h2>
            <span class="badge badge-teal">{{ $students->count() }} Siswa</span>
        </div>

        <div class="leaderboard-list">
            @forelse($students as $index => $student)
                @php $rank = $index + 1; @endphp
                <div class="leaderboard-item {{ $rank === 1 ? 'rank-1-item' : '' }}">
                    <div class="leaderboard-student">
                        <span class="rank-badge {{ $rank <= 3 ? 'rank-'.$rank : '' }}">{{ $rank }}</span>
                        <div>
                            <div class="student-name">{{ $student->name }}</div>
                            <div class="student-class">
                                NISN: {{ $student->nisn }}
                                &nbsp;·&nbsp;
                                Saldo: Rp {{ number_format($student->balance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="leaderboard-points">
                        <span>
                            <i class="bi bi-star" style="font-size:12px;color:var(--accent);margin-right:3px;"></i>
                            {{ number_format($student->points, 0, ',', '.') }}
                        </span>
                        <span style="display:block;font-size:9.5px;font-weight:600;color:var(--on-surface-variant);text-transform:uppercase;">Poin</span>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-person-x empty-icon"></i>
                    <h3 class="empty-title">Belum Ada Siswa</h3>
                    <p class="empty-desc">Belum ada siswa terdaftar di kelas asuhan Anda.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
