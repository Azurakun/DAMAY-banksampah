@extends('layouts.app')
@section('title', 'Performa Kelas — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Breadcrumb --}}
    <div style="margin-bottom: var(--s-20);">
        <a href="{{ route('manajer.dashboard') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Dasbor
        </a>
    </div>

    {{-- Header --}}
    <div class="page-header" style="margin-bottom: var(--s-24);">
        <div class="page-header-icon" style="background:var(--primary-container); color:var(--primary);">
            <i class="bi bi-bar-chart-line-fill"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-title">Performa Kelas</h1>
            <p class="page-subtitle">Peringkat kontribusi kelas dalam program daur ulang dan tabungan sekolah.</p>
        </div>
    </div>

    {{-- Main Performance Table Card --}}
    <div class="card" style="margin-top:0;">
        <div class="flex-between" style="margin-bottom:var(--s-16); flex-wrap:wrap; gap:var(--s-12);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8); margin:0;">
                <i class="bi bi-list-stars" style="color:var(--primary);"></i> Peringkat Kontribusi Seluruh Kelas
            </h2>
            
            <div style="display:flex; align-items:center; gap:var(--s-12); flex-wrap:wrap;">
                {{-- Sort Filter positioned near the table --}}
                <div style="display:inline-flex; align-items:center; gap:6px; background:var(--surface-dim); border:1px solid var(--outline-variant); border-radius:var(--r-md); padding:4px 8px;">
                    <span style="font-size:10px; font-weight:800; color:var(--on-surface-variant); text-transform:uppercase; margin-right:4px;">Urutkan:</span>
                    <a href="{{ route('manajer.performaKelas', ['sort' => 'points']) }}" class="btn {{ $sort === 'points' ? 'btn-primary' : 'btn-ghost' }}" style="padding: 4px 10px; font-size:11px; font-weight:700; height:28px; display:inline-flex; align-items:center; border-radius:var(--r-sm);">
                        Poin
                    </a>
                    <a href="{{ route('manajer.performaKelas', ['sort' => 'weight']) }}" class="btn {{ $sort === 'weight' ? 'btn-primary' : 'btn-ghost' }}" style="padding: 4px 10px; font-size:11px; font-weight:700; height:28px; display:inline-flex; align-items:center; border-radius:var(--r-sm);">
                        Berat
                    </a>
                    <a href="{{ route('manajer.performaKelas', ['sort' => 'balance']) }}" class="btn {{ $sort === 'balance' ? 'btn-primary' : 'btn-ghost' }}" style="padding: 4px 10px; font-size:11px; font-weight:700; height:28px; display:inline-flex; align-items:center; border-radius:var(--r-sm);">
                        Tabungan
                    </a>
                    <a href="{{ route('manajer.performaKelas', ['sort' => 'students']) }}" class="btn {{ $sort === 'students' ? 'btn-primary' : 'btn-ghost' }}" style="padding: 4px 10px; font-size:11px; font-weight:700; height:28px; display:inline-flex; align-items:center; border-radius:var(--r-sm);">
                        Siswa
                    </a>
                </div>
                <span class="badge badge-primary" style="height:28px; display:inline-flex; align-items:center; font-weight:700;">{{ $classes->count() }} Kelas</span>
            </div>
        </div>

        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:70px; text-align:center;">Rank</th>
                        <th>Nama Kelas</th>
                        <th style="text-align:center;">Jumlah Siswa</th>
                        <th style="text-align:right;">Total Berat disetor</th>
                        <th style="text-align:right;">Rata-rata /Siswa</th>
                        <th style="text-align:right;">Total Tabungan</th>
                        <th style="text-align:right;">Rata-rata Tabungan</th>
                        <th style="text-align:right;">Total Poin Kelas</th>
                        <th>Sampah Terfavorit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $index => $class)
                        @php $rank = $index + 1; @endphp
                        <tr style="{{ $rank <= 3 ? 'background: rgba(63, 125, 74, 0.04);' : '' }}">
                            <td style="text-align:center; font-weight:800; font-size:14px;">
                                @if($rank == 1) 🥇
                                @elseif($rank == 2) 🥈
                                @elseif($rank == 3) 🥉
                                @else {{ $rank }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('manajer.siswaTeraktif', ['class' => $class->class]) }}" style="color:var(--primary); text-decoration:underline; font-weight:800; font-size:14px;" title="Klik untuk lihat rincian siswa kelas {{ $class->class }}">
                                    {{ $class->class }}
                                </a>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge badge-teal" style="font-weight:700;">{{ $class->student_count }} Siswa</span>
                            </td>
                            <td style="text-align:right; font-weight:800; font-family:var(--font-mono);">
                                {{ number_format($class->total_weight, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align:right; font-family:var(--font-mono); color:var(--on-surface-variant); font-size:12.5px;">
                                {{ number_format($class->avg_weight, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--primary);">
                                Rp {{ number_format($class->total_balance, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right; font-family:var(--font-mono); color:var(--on-surface-variant); font-size:12.5px;">
                                Rp {{ number_format($class->avg_balance, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right;">
                                <span style="font-weight:800; color:var(--accent); display:inline-flex; align-items:center; gap:3px;">
                                    <i class="bi bi-star-fill" style="font-size:10px; color:var(--accent);"></i>
                                    {{ number_format($class->total_points, 0, ',', '.') }}
                                </span>
                            </td>
                            <td style="font-weight:600; color:var(--on-surface-variant);">
                                {{ $class->top_category_name }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:var(--s-32); color:var(--on-surface-variant);">
                                Belum ada data kelas yang dapat dihitung.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
