@extends('layouts.app')
@section('title', 'Detail Stok Gudang — EcoBank SMKN 2 Indramayu')

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
            <i class="bi bi-archive-fill"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-title">Persediaan Stok Gudang</h1>
            <p class="page-subtitle">Informasi lengkap timbulan sampah tersimpan, sirkulasi masuk-keluar, dan estimasi nilai persediaan.</p>
        </div>
    </div>

    @php
        $totalWeight = $categories->sum('available_stock');
        $totalValue = $categories->sum('estimated_value');
        $totalSetorWeight = $categories->sum('total_setor');
        $totalDistributedWeight = $categories->sum('total_distributed');
        $totalTransactions = $categories->sum('transactions_count');
    @endphp

    <style>
        .stok-detail-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--s-20);
            margin-bottom: var(--s-24);
        }
        @media (max-width: 1024px) {
            .stok-detail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 580px) {
            .stok-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- Summary Cards Grid --}}
    <div class="stok-detail-grid">
        <div class="stat-item-box" style="border-left: 4px solid var(--primary); margin:0;">
            <div class="stat-item-icon stat-icon-green"><i class="bi bi-box-seam"></i></div>
            <div class="stat-item-label">Total Stok Gudang</div>
            <div class="stat-item-value">{{ number_format($totalWeight, 2, ',', '.') }} kg</div>
            <div style="font-size:11px; color:var(--on-surface-variant); font-weight:600; margin-top:4px;">
                Tersimpan di tempat penampungan
            </div>
        </div>

        <div class="stat-item-box" style="border-left: 4px solid var(--accent); margin:0;">
            <div class="stat-item-icon stat-icon-amber"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-item-label">Estimasi Nilai Persediaan</div>
            <div class="stat-item-value" style="color:var(--accent);">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
            <div style="font-size:11px; color:var(--on-surface-variant); font-weight:600; margin-top:4px;">
                Berdasarkan harga beli saat ini
            </div>
        </div>

        <div class="stat-item-box" style="border-left: 4px solid var(--teal); margin:0;">
            <div class="stat-item-icon stat-icon-teal"><i class="bi bi-arrow-down-left-circle"></i></div>
            <div class="stat-item-label">Total Sampah Masuk</div>
            <div class="stat-item-value">{{ number_format($totalSetorWeight, 1, ',', '.') }} kg</div>
            <div style="font-size:11px; color:var(--on-surface-variant); font-weight:600; margin-top:4px;">
                Akumulasi setor siswa
            </div>
        </div>

        <div class="stat-item-box" style="border-left: 4px solid #0d6efd; margin:0;">
            <div class="stat-item-icon" style="background:rgba(13,110,253,0.1); color:#0d6efd;"><i class="bi bi-arrow-up-right-circle"></i></div>
            <div class="stat-item-label">Total Sampah Keluar</div>
            <div class="stat-item-value">{{ number_format($totalDistributedWeight, 1, ',', '.') }} kg</div>
            <div style="font-size:11px; color:var(--on-surface-variant); font-weight:600; margin-top:4px;">
                Telah didistribusikan / dijual
            </div>
        </div>
    </div>

    {{-- Search Card --}}
    <div class="card" style="margin-bottom: var(--s-20); padding: var(--s-16);">
        <form method="GET" action="{{ route('manajer.stok') }}" style="display:flex; gap:10px; width:100%;">
            <div style="flex:1; position:relative;">
                <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--outline); font-size:14px;"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari kategori sampah..." class="form-control" style="padding-left:36px; height:44px; margin:0; font-size:13.5px;">
            </div>
            <button type="submit" class="btn btn-primary" style="height:44px; padding:0 20px; font-size:13.5px; font-weight:700;">
                <i class="bi bi-filter"></i> Filter
            </button>
            @if($search)
                <a href="{{ route('manajer.stok') }}" class="btn btn-ghost" style="height:44px; font-size:13px; font-weight:700; display:inline-flex; align-items:center; justify-content:center;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
    <div class="card" style="margin-top:0;">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-table" style="color:var(--primary);"></i> Tabel Persediaan Kategori Sampah
            </h2>
            <span class="badge badge-primary">{{ $categories->count() }} Kategori</span>
        </div>

        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:70px; text-align:center;">Ikon</th>
                        <th>Nama Kategori</th>
                        <th style="text-align:right;">Harga Beli /kg</th>
                        <th style="text-align:right;">Total Masuk (Setor)</th>
                        <th style="text-align:right;">Total Keluar (Distribusi)</th>
                        <th style="text-align:right; background:rgba(63,125,74,0.05); color:var(--primary); font-weight:800;">Stok Gudang Aktif</th>
                        <th style="text-align:right;">Estimasi Nilai Stok</th>
                        <th style="text-align:center;">Vol. Transaksi</th>
                        <th style="text-align:center; width:150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        @php
                            $isLow = $category->available_stock <= 2.0;
                        @endphp
                        <tr>
                            <td style="text-align:center;">
                                <div style="width:36px; height:36px; border-radius:6px; background:var(--surface-dim); border:1px solid var(--outline-variant); overflow:hidden; display:inline-flex; align-items:center; justify-content:center;">
                                    @if($category->icon_image)
                                        <img src="{{ asset($category->icon_image) }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <i class="bi bi-recycle" style="font-size:18px; color:var(--primary);"></i>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <strong style="color:var(--on-surface); font-size:14px; display:block;">{{ $category->name }}</strong>
                                <span style="font-size:10px; color:var(--on-surface-variant); font-weight:600;">Key: {{ $category->key }}</span>
                            </td>
                            <td style="text-align:right; font-weight:700; font-family:var(--font-mono);">
                                Rp {{ number_format($category->price_per_kg, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right; font-family:var(--font-mono); color:var(--on-surface-variant);">
                                {{ number_format($category->total_setor, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align:right; font-family:var(--font-mono); color:var(--on-surface-variant);">
                                {{ number_format($category->total_distributed, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align:right; font-family:var(--font-mono); font-weight:800; background:rgba(63,125,74,0.03); color:var(--primary);">
                                {{ number_format($category->available_stock, 2, ',', '.') }} kg
                                @if($isLow && $category->available_stock > 0)
                                    <span style="display:block; font-size:8px; font-weight:800; color:#b45309; text-transform:uppercase;">Stok Rendah</span>
                                @elseif($category->available_stock == 0)
                                    <span style="display:block; font-size:8px; font-weight:800; color:var(--outline); text-transform:uppercase;">Habis</span>
                                @endif
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--accent); font-family:var(--font-mono);">
                                Rp {{ number_format($category->estimated_value, 0, ',', '.') }}
                            </td>
                            <td style="text-align:center;">
                                <span class="badge badge-teal" style="font-weight:700;">{{ $category->transactions_count }}x</span>
                            </td>
                            <td style="text-align:center;">
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <a href="{{ route('manajer.stok.show', $category->id) }}" class="btn btn-primary" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700; border-radius:var(--r-sm); display: inline-flex; align-items: center; gap: 4px; height:28px;">
                                        <i class="bi bi-clock-history"></i> Riwayat
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:var(--s-32); color:var(--on-surface-variant);">
                                <i class="bi bi-search" style="font-size:24px; display:block; margin-bottom:8px; color:var(--outline);"></i>
                                Kategori sampah yang Anda cari tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
