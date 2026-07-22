@extends('layouts.app')
@section('title', 'Kelola Distribusi Sampah — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Header --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="border-color:var(--accent);">
            <i class="bi bi-truck" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Distribusi Sampah Gudang</div>
            <div class="greeting-meta">Catatan sampah keluar dari bank sampah sekolah</div>
        </div>
        @if(Auth::user()->role === 'operator')
        <div style="margin-left:auto;">
            <a href="{{ route('operator.distributions.create') }}" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary); color:white; font-weight:700; box-shadow:var(--shadow-sm); display:inline-flex; align-items:center; gap:8px;">
                <i class="bi bi-plus-lg"></i> Catat Distribusi Baru
            </a>
        </div>
        @endif
    </div>

    {{-- Distributions Table Card --}}
    <div class="card" style="margin-top:var(--s-20);">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-clock-history" style="color:var(--primary);"></i> Riwayat Batch Distribusi
            </h2>
            <span class="badge badge-primary">{{ count($distributions) }} Batch</span>
        </div>

        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal Batch</th>
                        <th>Jalur Distribusi</th>
                        <th>Agen / Unit Penerima</th>
                        <th style="text-align:right;">Total Berat</th>
                        <th style="text-align:right;">Total Nilai Rupiah</th>
                        <th>Petugas Pencatat</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distributions as $dist)
                        <tr>
                            <td style="font-weight:700; font-family:var(--font-mono);">#{{ $dist->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($dist->batch_date)->translatedFormat('d F Y') }}</td>
                            <td>
                                @if($dist->route === 'agent')
                                    <span class="badge badge-success" style="background:var(--success-container); color:var(--success); border:1px solid var(--success); font-size:10px; white-space: nowrap;">
                                        <i class="bi bi-shop"></i> Jual ke Agen
                                    </span>
                                @else
                                    <span class="badge badge-teal" style="background:var(--teal-container); color:var(--teal); border:1px solid var(--teal); font-size:10px; white-space: nowrap;">
                                        <i class="bi bi-recycle"></i> Unit Internal
                                    </span>
                                @endif
                            </td>
                            <td style="font-weight:600;">{{ $dist->agent_name ?: '-' }}</td>
                            <td style="text-align:right; font-weight:700; font-family:var(--font-mono);">
                                {{ number_format($dist->total_weight, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align:right; font-weight:700; font-family:var(--font-mono); color:{{ $dist->route === 'agent' ? 'var(--success)' : 'inherit' }}">
                                Rp {{ number_format($dist->total_value, 0, ',', '.') }}
                            </td>
                            <td>{{ $dist->creator->name ?? 'N/A' }}</td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:6px; justify-content:center; align-items:center; flex-wrap:wrap;">
                                    <a href="{{ route(Auth::user()->role . '.distributions.show', $dist->id) }}" class="btn btn-outline" style="padding: 4px 10px; font-size: 12px; font-weight: 700; border: 1.5px solid var(--outline); background: transparent; color: var(--primary); text-decoration: none; border-radius:var(--r-sm); display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <a href="{{ route(Auth::user()->role . '.distributions.receipt', $dist->id) }}" class="btn btn-outline" style="padding: 4px 10px; font-size: 12px; font-weight: 700; border: 1.5px solid var(--primary); background: rgba(63,125,74,0.06); color: var(--primary); text-decoration: none; border-radius:var(--r-sm); display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-printer"></i> Struk
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:var(--s-32); color:var(--on-surface-variant);">
                                <i class="bi bi-info-circle" style="font-size:24px; display:block; margin-bottom:8px; color:var(--outline);"></i>
                                Belum ada catatan distribusi sampah keluar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
