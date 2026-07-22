@extends('layouts.app')
@section('title', 'Riwayat Stok Kategori: ' . $category->name . ' — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Breadcrumb --}}
    <div style="margin-bottom: var(--s-20);">
        <a href="{{ route('manajer.stok') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Persediaan Gudang
        </a>
    </div>

    {{-- Header --}}
    <div class="page-header" style="margin-bottom: var(--s-24); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--s-12);">
        <div style="display:flex; gap:16px; align-items:center;">
            <div style="width:54px; height:54px; border-radius:12px; background:var(--primary-container); border:1.5px solid var(--primary); overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                @if($category->icon_image)
                    <img src="{{ asset($category->icon_image) }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <i class="bi bi-recycle" style="font-size:24px; color:var(--primary);"></i>
                @endif
            </div>
            <div>
                <h1 class="page-title" style="font-size:24px; font-weight:900;">Riwayat Stok: {{ $category->name }}</h1>
                <p class="page-subtitle">Rincian mutasi setoran nasabah (inflow) dan pengiriman distribusi ke agen/pengolahan (outflow).</p>
            </div>
        </div>
        <div class="badge badge-primary" style="font-size:12px; font-weight:800; padding:6px 14px; background:var(--primary-container); color:var(--primary);">
            Harga Saat Ini: Rp {{ number_format($category->price_per_kg, 0, ',', '.') }} /kg
        </div>
    </div>

    <style>
        .stok-category-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--s-20);
            margin-bottom: var(--s-28);
        }
        @media (max-width: 700px) {
            .stok-category-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- Category Mutasi Stats Summary --}}
    <div class="stok-category-grid">
        
        {{-- Total Setoran Masuk --}}
        <div class="card" style="padding:var(--s-16); border-top: 4px solid var(--teal); margin:0;">
            <span style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--on-surface-variant); display:block; margin-bottom:4px;">Total Masuk (Setoran)</span>
            <strong style="font-size:20px; font-family:var(--font-mono); color:var(--teal);">{{ number_format($totalSetor, 2, ',', '.') }} kg</strong>
            <span style="display:block; font-size:11px; color:var(--on-surface-variant); margin-top:2px;">Senilai: Rp {{ number_format($totalSetorValue, 0, ',', '.') }}</span>
        </div>

        {{-- Total Distribusi Keluar --}}
        <div class="card" style="padding:var(--s-16); border-top: 4px solid #0d6efd; margin:0;">
            <span style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--on-surface-variant); display:block; margin-bottom:4px;">Total Keluar (Distribusi)</span>
            <strong style="font-size:20px; font-family:var(--font-mono); color:#0d6efd;">{{ number_format($totalDistributed, 2, ',', '.') }} kg</strong>
            <span style="display:block; font-size:11px; color:var(--on-surface-variant); margin-top:2px;">Senilai: Rp {{ number_format($totalDistributedValue, 0, ',', '.') }}</span>
        </div>

        {{-- Sisa Stok Gudang --}}
        <div class="card" style="padding:var(--s-16); border-top: 4px solid var(--primary); margin:0; background:rgba(63,125,74,0.03);">
            <span style="font-size:10px; font-weight:700; text-transform:uppercase; color:var(--primary); display:block; margin-bottom:4px;">Stok Tersedia Saat Ini</span>
            <strong style="font-size:20px; font-family:var(--font-mono); color:var(--primary);">{{ number_format($availableStock, 2, ',', '.') }} kg</strong>
            <span style="display:block; font-size:11px; color:var(--on-surface-variant); margin-top:2px;">Estimasi Nilai: Rp {{ number_format($estimatedValue, 0, ',', '.') }}</span>
        </div>

    </div>

    {{-- Stacked History Panels --}}
    <div style="display:flex; flex-direction:column; gap:var(--s-24);">
        
        {{-- Section 1: Inflow (Setoran Masuk) --}}
        <div class="card" style="margin:0;">
            <div class="flex-between" style="margin-bottom:var(--s-16);">
                <h3 style="font-size:15px; font-weight:800; color:var(--primary); display:flex; align-items:center; gap:6px; margin:0;">
                    <i class="bi bi-arrow-down-left-circle-fill" style="color:var(--teal);"></i> Riwayat Setoran Siswa (Sampah Masuk)
                </h3>
                <span class="badge badge-teal" style="font-weight:700;">Halaman {{ $deposits->currentPage() }} dari {{ $deposits->lastPage() }}</span>
            </div>

            <div class="table-overflow">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:80px; text-align:center;">ID Transaksi</th>
                            <th>Tanggal Setor</th>
                            <th>Nama Siswa (Nasabah)</th>
                            <th>Kelas</th>
                            <th style="text-align:right;">Timbangan Berat (kg)</th>
                            <th style="text-align:right;">Harga/kg Saat Setor</th>
                            <th style="text-align:right;">Kredit Tabungan (Rp)</th>
                            <th style="text-align:right;">Poin Reward</th>
                            <th>Petugas / Operator</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center; width:90px;">Struk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $tx)
                            <tr>
                                <td style="text-align:center; font-family:var(--font-mono); font-weight:700;">#{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                <td style="font-weight:700; color:var(--on-surface);">{{ $tx->student->name }}</td>
                                <td style="font-weight:600;">{{ $tx->student->class ?? '-' }}</td>
                                <td style="text-align:right; font-family:var(--font-mono); font-weight:700; color:var(--teal);">
                                    {{ number_format($tx->weight, 2, ',', '.') }} kg
                                </td>
                                <td style="text-align:right; font-family:var(--font-mono); color:var(--on-surface-variant);">
                                    Rp {{ number_format($tx->wasteCategory->price_per_kg ?? 0, 0, ',', '.') }}
                                </td>
                                <td style="text-align:right; font-family:var(--font-mono); font-weight:700; color:var(--primary);">
                                    +Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td style="text-align:right; font-weight:800; color:var(--accent);">
                                    +{{ number_format($tx->points, 0, ',', '.') }}
                                </td>
                                <td>{{ $tx->operator->name ?? 'Sistem' }}</td>
                                <td style="text-align:center;">
                                    <span class="transaction-status {{ strtolower($tx->status) }}" style="font-size:10px; display:inline-block; padding:2px 8px; border-radius:4px;">
                                        {{ $tx->status }}
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <a href="{{ route('manajer.transaction.receipt', $tx->id) }}" class="btn btn-outline" style="padding: 3px 6px; font-size: 11px; font-weight: 700; border: 1.5px solid var(--outline); background: transparent; color: var(--primary); text-decoration: none; border-radius:var(--r-sm); display: inline-flex; align-items: center; gap: 3px; height:24px;">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" style="text-align:center; padding:var(--s-24); color:var(--on-surface-variant);">
                                    Belum ada catatan setoran masuk untuk kategori sampah ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deposits->hasPages())
                <div style="margin-top:var(--s-16); display:flex; justify-content:center;">
                    {{ $deposits->links() }}
                </div>
            @endif
        </div>

        {{-- Section 2: Outflow (Distribusi Keluar) --}}
        <div class="card" style="margin:0;">
            <div class="flex-between" style="margin-bottom:var(--s-16);">
                <h3 style="font-size:15px; font-weight:800; color:var(--primary); display:flex; align-items:center; gap:6px; margin:0;">
                    <i class="bi bi-arrow-up-right-circle-fill" style="color:#0d6efd;"></i> Riwayat Distribusi Gudang (Sampah Keluar)
                </h3>
                <span class="badge badge-primary" style="font-weight:700;">Halaman {{ $distributions->currentPage() }} dari {{ $distributions->lastPage() }}</span>
            </div>

            <div class="table-overflow">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:100px; text-align:center;">ID Batch</th>
                            <th>Tanggal Kirim</th>
                            <th>Nama Agen / Penerima</th>
                            <th>Jalur Tujuan</th>
                            <th style="text-align:right;">Berat Keluar (kg)</th>
                            <th style="text-align:right;">Harga Jual /kg</th>
                            <th style="text-align:right;">Total Penerimaan Kas (Rp)</th>
                            <th>Catatan Batch</th>
                            <th>Petugas / Operator</th>
                            <th style="text-align:center; width:90px;">Struk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($distributions as $item)
                            <tr>
                                <td style="text-align:center; font-family:var(--font-mono); font-weight:700; color:#0d6efd;">
                                    #DIST-{{ str_pad($item->distribution_id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->distribution->batch_date)->translatedFormat('d F Y') }}</td>
                                <td>
                                    <strong style="color:var(--on-surface);">{{ $item->distribution->agent_name ?: 'Unit Pengolahan Internal' }}</strong>
                                </td>
                                <td>
                                    @if($item->distribution->route === 'agent')
                                        🏪 Agen Komersial
                                    @else
                                        ♻️ Unit Pengolahan Internal
                                    @endif
                                </td>
                                <td style="text-align:right; font-family:var(--font-mono); font-weight:700; color:#0d6efd;">
                                    {{ number_format($item->weight, 2, ',', '.') }} kg
                                </td>
                                <td style="text-align:right; font-family:var(--font-mono); color:var(--on-surface-variant);">
                                    Rp {{ number_format($item->price_per_kg, 0, ',', '.') }}
                                </td>
                                <td style="text-align:right; font-family:var(--font-mono); font-weight:700; color:var(--primary);">
                                    Rp {{ number_format($item->value, 0, ',', '.') }}
                                </td>
                                <td style="font-size:12px; font-style:italic; color:var(--on-surface-variant);">
                                    {{ $item->distribution->notes ?: '—' }}
                                </td>
                                <td>{{ $item->distribution->creator->name ?? 'N/A' }}</td>
                                <td style="text-align:center;">
                                    <a href="{{ route('manajer.distributions.receipt', $item->distribution_id) }}" class="btn btn-outline" style="padding: 3px 6px; font-size: 11px; font-weight: 700; border: 1.5px solid var(--outline); background: transparent; color: var(--primary); text-decoration: none; border-radius:var(--r-sm); display: inline-flex; align-items: center; gap: 3px; height:24px;">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align:center; padding:var(--s-24); color:var(--on-surface-variant);">
                                    Kategori sampah ini belum pernah didistribusikan keluar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($distributions->hasPages())
                <div style="margin-top:var(--s-16); display:flex; justify-content:center;">
                    {{ $distributions->links() }}
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
