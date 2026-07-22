@extends('layouts.app')
@section('title', 'Leaderboard Siswa Teraktif — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Breadcrumb --}}
    <div style="margin-bottom: var(--s-20);">
        <a href="{{ route('manajer.dashboard') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:var(--primary); text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Dasbor
        </a>
    </div>

    {{-- Header --}}
    <div class="page-header" style="margin-bottom: var(--s-24);">
        <div class="page-header-icon" style="background:var(--primary-container); color:var(--primary);">
            <i class="bi bi-trophy-fill"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-title">Leaderboard Nasabah Teraktif</h1>
            <p class="page-subtitle">Daftar peringkat kontribusi siswa se-sekolah dalam daur ulang sampah dan pengumpulan poin.</p>
        </div>
    </div>

    {{-- Visual Podium for Top 3 (Only visible if showing first page without specific filters to keep podium authentic) --}}
    @if($students->currentPage() === 1 && !$search && !$classFilter && !$leagueFilter && $students->count() >= 3)
        @php
            $podiumList = $students->take(3)->values();
        @endphp
        <div style="display:flex; justify-content:center; align-items:flex-end; gap:var(--s-16); margin-bottom:var(--s-32); padding-top:var(--s-24); overflow-x:auto;">
            
            {{-- Rank 2 (Silver) --}}
            @if(isset($podiumList[1]))
                <div class="card" style="width:200px; text-align:center; padding:var(--s-16); border-top: 4px solid #94a3b8; display:flex; flex-direction:column; align-items:center; margin:0;">
                    <span style="font-size:32px;">🥈</span>
                    <div class="user-avatar-circle" style="width:54px; height:54px; border-radius:50%; background:#94a3b8; color:white; font-size:18px; font-weight:800; display:flex; align-items:center; justify-content:center; margin-bottom:8px; overflow:hidden;">
                        @if($podiumList[1]->avatar)
                            <img src="{{ asset($podiumList[1]->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            {{ strtoupper(substr($podiumList[1]->name, 0, 1)) }}
                        @endif
                    </div>
                    <strong style="font-size:13px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:100%;">{{ $podiumList[1]->name }}</strong>
                    <span style="font-size:11px; color:var(--on-surface-variant);">{{ $podiumList[1]->class }}</span>
                    <div style="margin-top:10px; background:var(--surface-dim); border-radius:6px; padding:4px 8px; font-size:12px; font-weight:800; color:var(--primary);">
                        ⭐ {{ number_format($podiumList[1]->points, 0, ',', '.') }}
                    </div>
                </div>
            @endif

            {{-- Rank 1 (Gold) --}}
            @if(isset($podiumList[0]))
                <div class="card" style="width:220px; text-align:center; padding:var(--s-24) var(--s-16); border-top: 5px solid var(--accent); display:flex; flex-direction:column; align-items:center; margin:0; box-shadow:0 8px 32px rgba(255, 183, 27, 0.15); transform:scale(1.05);">
                    <span style="font-size:40px; position:absolute; top:-24px;">👑</span>
                    <span style="font-size:36px; margin-top:8px;">🥇</span>
                    <div class="user-avatar-circle" style="width:64px; height:64px; border-radius:50%; background:var(--accent); color:white; font-size:22px; font-weight:800; display:flex; align-items:center; justify-content:center; margin-bottom:8px; overflow:hidden; border:2px solid var(--accent);">
                        @if($podiumList[0]->avatar)
                            <img src="{{ asset($podiumList[0]->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            {{ strtoupper(substr($podiumList[0]->name, 0, 1)) }}
                        @endif
                    </div>
                    <strong style="font-size:14.5px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:100%;">{{ $podiumList[0]->name }}</strong>
                    <span style="font-size:11px; color:var(--on-surface-variant);">{{ $podiumList[0]->class }}</span>
                    <div style="margin-top:10px; background:rgba(255, 183, 27, 0.12); border-radius:6px; padding:6px 12px; font-size:13.5px; font-weight:900; color:var(--accent); border:1.5px solid var(--accent);">
                        ⭐ {{ number_format($podiumList[0]->points, 0, ',', '.') }}
                    </div>
                </div>
            @endif

            {{-- Rank 3 (Bronze) --}}
            @if(isset($podiumList[2]))
                <div class="card" style="width:200px; text-align:center; padding:var(--s-16); border-top: 4px solid #b45309; display:flex; flex-direction:column; align-items:center; margin:0;">
                    <span style="font-size:32px;">🥉</span>
                    <div class="user-avatar-circle" style="width:54px; height:54px; border-radius:50%; background:#b45309; color:white; font-size:18px; font-weight:800; display:flex; align-items:center; justify-content:center; margin-bottom:8px; overflow:hidden;">
                        @if($podiumList[2]->avatar)
                            <img src="{{ asset($podiumList[2]->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            {{ strtoupper(substr($podiumList[2]->name, 0, 1)) }}
                        @endif
                    </div>
                    <strong style="font-size:13px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:100%;">{{ $podiumList[2]->name }}</strong>
                    <span style="font-size:11px; color:var(--on-surface-variant);">{{ $podiumList[2]->class }}</span>
                    <div style="margin-top:10px; background:var(--surface-dim); border-radius:6px; padding:4px 8px; font-size:12px; font-weight:800; color:var(--primary);">
                        ⭐ {{ number_format($podiumList[2]->points, 0, ',', '.') }}
                    </div>
                </div>
            @endif

        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card" style="margin-bottom: var(--s-20); padding: var(--s-16);">
        <form method="GET" action="{{ route('manajer.siswaTeraktif') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--s-12); align-items:end;">
            
            {{-- Name / NISN Search --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11px; text-transform:uppercase; color:var(--on-surface-variant); margin-bottom:4px;">Cari Nama / NISN</label>
                <div style="position:relative;">
                    <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--outline); font-size:13px;"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama siswa..." class="form-control" style="padding-left:36px; height:38px; margin:0; font-size:13px;">
                </div>
            </div>

            {{-- Class --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11px; text-transform:uppercase; color:var(--on-surface-variant); margin-bottom:4px;">Kelas</label>
                <select name="class" class="form-control" style="height:38px; margin:0; font-size:13px; padding:0 10px;">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c }}" {{ $classFilter === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            {{-- League --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11px; text-transform:uppercase; color:var(--on-surface-variant); margin-bottom:4px;">Liga Gamifikasi</label>
                <select name="league" class="form-control" style="height:38px; margin:0; font-size:13px; padding:0 10px;">
                    <option value="">Semua Liga</option>
                    @foreach($leagues as $l)
                        <option value="{{ $l }}" {{ $leagueFilter === $l ? 'selected' : '' }}>{{ ucfirst($l) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter & Clear Buttons --}}
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary" style="height:38px; font-size:13px; font-weight:700; flex:1;">
                    <i class="bi bi-search"></i> Cari
                </button>
                @if($search || $classFilter || $leagueFilter)
                    <a href="{{ route('manajer.siswaTeraktif') }}" class="btn btn-ghost" style="height:38px; font-size:13px; font-weight:700; display:inline-flex; align-items:center; justify-content:center;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Main Leaderboard List --}}
    <div class="card" style="margin-top:0;">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-trophy" style="color:var(--accent);"></i> Peringkat Aktif Nasabah
            </h2>
            <span class="badge badge-accent">Poin Terakumulasi</span>
        </div>

        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px; text-align:center;">Peringkat</th>
                        <th>Nasabah</th>
                        <th>NISN</th>
                        <th>Kelas</th>
                        <th>Liga Gamifikasi</th>
                        <th style="text-align:right;">Total Setoran Sampah</th>
                        <th style="text-align:right;">Weekly Points</th>
                        <th style="text-align:right;">Total Poin (Tabungan)</th>
                        <th style="text-align:right;">Saldo Tabungan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                        @php 
                            $rank = (($students->currentPage() - 1) * $students->perPage()) + ($index + 1); 
                        @endphp
                        <tr style="{{ $rank <= 3 && $students->currentPage() === 1 ? 'background: rgba(255, 183, 27, 0.04);' : '' }}">
                            <td style="text-align:center; font-weight:800; font-size:13.5px;">
                                @if($rank === 1) 🥇
                                @elseif($rank === 2) 🥈
                                @elseif($rank === 3) 🥉
                                @else {{ $rank }}
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="user-avatar-circle" style="width:32px; height:32px; border-radius:50%; background:var(--primary); color:white; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                                        @if($student->avatar)
                                            <img src="{{ asset($student->avatar) }}" style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <strong style="color:var(--on-surface);">{{ $student->name }}</strong>
                                </div>
                            </td>
                            <td style="font-family:var(--font-mono); font-size:12.5px; color:var(--on-surface-variant);">{{ $student->nisn ?? '-' }}</td>
                            <td style="font-weight:600;">{{ $student->class }}</td>
                            <td>
                                @php
                                    $leagueBadge = 'badge-primary';
                                    if(strtolower($student->league) === 'gold') $leagueBadge = 'badge-accent';
                                    elseif(strtolower($student->league) === 'silver') $leagueBadge = 'badge-teal';
                                @endphp
                                <span class="badge {{ $leagueBadge }}" style="font-size:10px; font-weight:800; text-transform:uppercase;">
                                    {{ $student->league ?? 'Green' }}
                                </span>
                            </td>
                            <td style="text-align:right; font-weight:700; font-family:var(--font-mono);">
                                {{ number_format($student->total_weight, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--accent);">
                                +{{ number_format($student->weekly_points, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right;">
                                <strong style="color:var(--accent); display:inline-flex; align-items:center; gap:2px; font-size:13.5px;">
                                    ⭐ {{ number_format($student->points, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td style="text-align:right; font-weight:800; color:var(--primary); font-family:var(--font-mono);">
                                Rp {{ number_format($student->balance, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:var(--s-32); color:var(--on-surface-variant);">
                                <i class="bi bi-person-slash" style="font-size:24px; display:block; margin-bottom:8px; color:var(--outline);"></i>
                                Tidak ada siswa terdaftar atau cocok dengan pencarian Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div style="margin-top:var(--s-20); display:flex; justify-content:center;">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
