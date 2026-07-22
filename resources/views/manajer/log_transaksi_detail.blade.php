@extends('layouts.app')
@section('title', 'Log Transaksi Sekolah — EcoBank SMKN 2 Indramayu')

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
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-title">Log Transaksi Lengkap</h1>
            <p class="page-subtitle">Jurnal terintegrasi setoran sampah, tabungan masuk, dan pencairan dana di lingkungan sekolah.</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card" style="margin-bottom: var(--s-24); padding: var(--s-20);">
        <h3 style="font-size:14px; font-weight:800; color:var(--primary); margin-bottom:var(--s-16); display:flex; align-items:center; gap:8px;">
            <i class="bi bi-funnel-fill"></i> Penyaringan Data Transaksi
        </h3>
        
        <form method="GET" action="{{ route('manajer.logTransaksi') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--s-16);">
            
            {{-- Name / NISN Search --}}
            <div style="grid-column: span 2;">
                <label class="form-label" style="font-weight:700; font-size:11.5px; text-transform:uppercase; color:var(--on-surface-variant);">Pencarian Nasabah</label>
                <div style="position:relative;">
                    <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--outline); font-size:13px;"></i>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Masukkan nama siswa atau NISN..." class="form-control" style="padding-left:36px; height:40px; margin:0; font-size:13px;">
                </div>
            </div>

            {{-- Transaction Type --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11.5px; text-transform:uppercase; color:var(--on-surface-variant);">Jenis Transaksi</label>
                <select name="type" class="form-control" style="height:40px; margin:0; font-size:13px; padding:0 10px;">
                    <option value="">Semua Jenis</option>
                    <option value="setor" {{ $filters['type'] === 'setor' ? 'selected' : '' }}>Setoran Sampah (Setor)</option>
                    <option value="tarik" {{ $filters['type'] === 'tarik' ? 'selected' : '' }}>Penarikan Saldo (Tarik)</option>
                </select>
            </div>

            {{-- Transaction Status --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11.5px; text-transform:uppercase; color:var(--on-surface-variant);">Status</label>
                <select name="status" class="form-control" style="height:40px; margin:0; font-size:13px; padding:0 10px;">
                    <option value="">Semua Status</option>
                    <option value="Berhasil" {{ $filters['status'] === 'Berhasil' ? 'selected' : '' }}>Berhasil</option>
                    <option value="Menunggu" {{ $filters['status'] === 'Menunggu' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="Batal" {{ $filters['status'] === 'Batal' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            {{-- Class --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11.5px; text-transform:uppercase; color:var(--on-surface-variant);">Kelas</label>
                <select name="class" class="form-control" style="height:40px; margin:0; font-size:13px; padding:0 10px;">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c }}" {{ $filters['class'] === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Category --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11.5px; text-transform:uppercase; color:var(--on-surface-variant);">Kategori Sampah</label>
                <select name="waste_category_id" class="form-control" style="height:40px; margin:0; font-size:13px; padding:0 10px;">
                    <option value="">Semua Sampah</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $filters['waste_category_id'] == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Start Date --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11.5px; text-transform:uppercase; color:var(--on-surface-variant);">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-control" style="height:40px; margin:0; font-size:13px; padding:0 10px;">
            </div>

            {{-- End Date --}}
            <div>
                <label class="form-label" style="font-weight:700; font-size:11.5px; text-transform:uppercase; color:var(--on-surface-variant);">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-control" style="height:40px; margin:0; font-size:13px; padding:0 10px;">
            </div>

            {{-- Action buttons --}}
            <div style="grid-column: 1 / -1; display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
                @if(array_filter($filters))
                    <a href="{{ route('manajer.logTransaksi') }}" class="btn btn-ghost" style="height:40px; font-size:13px; font-weight:700; display:inline-flex; align-items:center; justify-content:center;">
                        Bersihkan Filter
                    </a>
                @endif
                <button type="submit" class="btn btn-primary" style="height:40px; font-size:13px; font-weight:700; padding:0 24px;">
                    <i class="bi bi-search"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Filter-based Dynamic Aggregate Statistics --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--s-20); margin-bottom: var(--s-24);">
        <div class="card" style="padding:var(--s-16); border-top: 4px solid var(--primary); display:flex; gap:16px; align-items:center; margin-top:0;">
            <div style="width:42px; height:42px; border-radius:50%; background:rgba(63,125,74,0.1); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="bi bi-recycle"></i>
            </div>
            <div>
                <span style="font-size:10px; font-weight:600; text-transform:uppercase; color:var(--on-surface-variant); display:block;">Volume Daur Ulang Terfilter</span>
                <strong style="font-size:18px; font-family:var(--font-mono); color:var(--on-surface);">{{ number_format($filteredWeight, 2, ',', '.') }} kg</strong>
            </div>
        </div>

        <div class="card" style="padding:var(--s-16); border-top: 4px solid var(--primary); display:flex; gap:16px; align-items:center; margin-top:0;">
            <div style="width:42px; height:42px; border-radius:50%; background:rgba(63,125,74,0.1); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <span style="font-size:10px; font-weight:600; text-transform:uppercase; color:var(--on-surface-variant); display:block;">Tabungan Masuk Terfilter</span>
                <strong style="font-size:18px; font-family:var(--font-mono); color:var(--primary);">Rp {{ number_format($filteredSetorAmount, 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="card" style="padding:var(--s-16); border-top: 4px solid var(--danger); display:flex; gap:16px; align-items:center; margin-top:0;">
            <div style="width:42px; height:42px; border-radius:50%; background:rgba(220,53,69,0.1); color:var(--danger); display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <span style="font-size:10px; font-weight:600; text-transform:uppercase; color:var(--on-surface-variant); display:block;">Penarikan Dana Terfilter</span>
                <strong style="font-size:18px; font-family:var(--font-mono); color:var(--danger);">Rp {{ number_format($filteredTarikAmount, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    {{-- Main Ledger List --}}
    <div class="card" style="margin-top:0;">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-journal-text" style="color:var(--primary);"></i> Jurnal Riwayat Transaksi
            </h2>
            <span class="badge badge-primary">Halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }}</span>
        </div>

        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:80px; text-align:center;">ID</th>
                        <th>Tanggal</th>
                        <th>Nasabah (Siswa)</th>
                        <th>Kelas</th>
                        <th>Tipe</th>
                        <th>Sampah / Keterangan</th>
                        <th style="text-align:right;">Timbangan</th>
                        <th style="text-align:right;">Rupiah</th>
                        <th style="text-align:right;">Poin</th>
                        <th>Petugas</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td style="text-align:center; font-weight:700; font-family:var(--font-mono);">#{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td style="font-weight:700; color:var(--on-surface);">{{ $tx->student->name }}</td>
                            <td style="font-weight:600;">{{ $tx->student->class ?? 'N/A' }}</td>
                            <td>
                                @if($tx->type === 'setor')
                                    <span class="badge badge-success" style="background:var(--success-container); color:var(--success); font-size:10px;">Setor</span>
                                @else
                                    <span class="badge badge-danger" style="background:var(--danger-container); color:var(--danger); font-size:10px;">Tarik</span>
                                @endif
                            </td>
                            <td>{{ $tx->type === 'setor' ? ($tx->wasteCategory->name ?? 'Sampah') : ($tx->note ?: 'Tarik Saldo') }}</td>
                            <td style="text-align:right; font-family:var(--font-mono); font-weight:700;">
                                {{ $tx->type === 'setor' ? number_format($tx->weight, 2, ',', '.') . ' kg' : '-' }}
                            </td>
                            <td style="text-align:right; font-weight:700; color:{{ $tx->type === 'setor' ? 'var(--primary)' : 'var(--danger)' }}; font-family:var(--font-mono);">
                                {{ $tx->type === 'setor' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right; font-weight:800; color:var(--accent);">
                                {{ $tx->points > 0 ? '+' . number_format($tx->points, 0, ',', '.') : '-' }}
                            </td>
                            <td>{{ $tx->operator->name ?? 'Sistem' }}</td>
                            <td style="text-align:center;">
                                <span class="transaction-status {{ strtolower($tx->status) }}" style="font-size:10px; display:inline-block; padding:2px 8px; border-radius:4px;">
                                    {{ $tx->status }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('manajer.transaction.receipt', $tx->id) }}" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px; font-weight: 700; border: 1.5px solid var(--outline); background: transparent; color: var(--primary); text-decoration: none; border-radius:var(--r-sm); display: inline-flex; align-items: center; gap: 3px;">
                                    <i class="bi bi-printer"></i> Struk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="text-align:center; padding:var(--s-32); color:var(--on-surface-variant);">
                                <i class="bi bi-search" style="font-size:24px; display:block; margin-bottom:8px; color:var(--outline);"></i>
                                Transaksi tidak ditemukan sesuai dengan filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Laravel Pagination Render --}}
        @if($transactions->hasPages())
            <div style="margin-top:var(--s-20); display:flex; justify-content:center;">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
