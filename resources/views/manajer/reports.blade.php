@extends('layouts.app')
@section('title', 'Laporan Dinamis Sekolah — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Header --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="border-color:var(--accent);">
            <i class="bi bi-file-earmark-bar-graph" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Konsol Laporan Dinamis</div>
            <div class="greeting-meta">Analisis dan filter data transaksi bank sampah sekolah secara real-time</div>
        </div>
    </div>

    {{-- Filter Console Card --}}
    <div class="card" style="margin-top: var(--s-20); border-top: 5px solid var(--accent);">
        <h3 style="font-family:var(--font-display); font-size:16px; font-weight:800; color:var(--primary); margin-bottom:var(--s-16); display:flex; align-items:center; gap:8px;">
            <i class="bi bi-sliders" style="color:var(--accent);"></i> Filter Parameter Transaksi
        </h3>

        <form action="{{ route('manajer.reports') }}" method="GET" id="report-filter-form">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--s-12);">
                
                {{-- Start Date --}}
                <div class="form-group">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}" style="padding: 6px 10px; font-size:13px;">
                </div>

                {{-- End Date --}}
                <div class="form-group">
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}" style="padding: 6px 10px; font-size:13px;">
                </div>

                {{-- Class --}}
                <div class="form-group">
                    <label for="class" class="form-label">Kelas Siswa</label>
                    <select id="class" name="class" class="form-control" style="padding: 6px 10px; font-size:13px;">
                        <option value="">Semua Kelas</option>
                        @foreach($classrooms as $room)
                            <option value="{{ $room->name }}" {{ request('class') == $room->name ? 'selected' : '' }}>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Waste Category --}}
                <div class="form-group">
                    <label for="waste_category_id" class="form-label">Kategori Sampah</label>
                    <select id="waste_category_id" name="waste_category_id" class="form-control" style="padding: 6px 10px; font-size:13px;">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('waste_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Transaction Type --}}
                <div class="form-group">
                    <label for="type" class="form-label">Tipe Transaksi</label>
                    <select id="type" name="type" class="form-control" style="padding: 6px 10px; font-size:13px;">
                        <option value="">Semua Tipe</option>
                        <option value="setor" {{ request('type') == 'setor' ? 'selected' : '' }}>Setor (Tabung Sampah)</option>
                        <option value="tarik" {{ request('type') == 'tarik' ? 'selected' : '' }}>Tarik (Tarik Dana)</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control" style="padding: 6px 10px; font-size:13px;">
                        <option value="">Semua Status</option>
                        <option value="Berhasil" {{ request('status') == 'Berhasil' ? 'selected' : '' }}>Berhasil</option>
                        <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="Batal" {{ request('status') == 'Batal' ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>

            </div>

            <div style="margin-top:var(--s-16); display:flex; gap:var(--s-8); justify-content:flex-end;">
                <a href="{{ route('manajer.reports') }}" class="btn btn-outline" style="border: 1.5px solid var(--outline-variant); color: var(--on-background); font-weight:700; text-decoration:none; padding: 6px 16px; border-radius:var(--r-sm); font-size:13px; display:inline-flex; align-items:center; gap:4px;">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
                <button type="submit" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary); color:white; font-weight:700; padding: 6px 20px; border-radius:var(--r-sm); font-size:13px; box-shadow:var(--shadow-sm); display:inline-flex; align-items:center; gap:4px;">
                    <i class="bi bi-search"></i> Cari Data
                </button>
            </div>
        </form>
    </div>

    {{-- Filter Summary Stats --}}
    @if(count($transactions) > 0)
        <div class="operator-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-top: var(--s-20);">
            <div class="stat-item-box">
                <div class="stat-item-icon stat-icon-teal"><i class="bi bi-recycle"></i></div>
                <div class="stat-item-label">Total Sampah Terfilter</div>
                <div class="stat-item-value">{{ number_format($totalWeight, 2, ',', '.') }} <span style="font-size:12px;">kg</span></div>
            </div>
            <div class="stat-item-box">
                <div class="stat-item-icon stat-icon-green"><i class="bi bi-box-arrow-in-down"></i></div>
                <div class="stat-item-label">Total Nilai Setor</div>
                <div class="stat-item-value" style="font-size:15px;">Rp {{ number_format($totalSetorAmount, 0, ',', '.') }}</div>
            </div>
            <div class="stat-item-box">
                <div class="stat-item-icon stat-icon-amber"><i class="bi bi-box-arrow-up"></i></div>
                <div class="stat-item-label">Total Penarikan Dana</div>
                <div class="stat-item-value" style="font-size:15px;">Rp {{ number_format($totalTarikAmount, 0, ',', '.') }}</div>
            </div>
        </div>
    @endif

    {{-- Transactions Table --}}
    <div class="card" style="margin-top:var(--s-20);">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-list-task" style="color:var(--primary);"></i> Hasil Penelusuran Laporan
            </h2>
            
            {{-- Export Buttons --}}
            @if(count($transactions) > 0)
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('manajer.reports.excel', request()->all()) }}" class="btn btn-outline" style="border:1.5px solid #22c55e; color:#15803d; font-weight:700; text-decoration:none; padding:4px 10px; font-size:12px; border-radius:var(--r-sm); display:inline-flex; align-items:center; gap:4px;">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </a>
                    <a href="{{ route('manajer.reports.pdf', request()->all()) }}" class="btn btn-outline" style="border:1.5px solid #ef4444; color:#b91c1c; font-weight:700; text-decoration:none; padding:4px 10px; font-size:12px; border-radius:var(--r-sm); display:inline-flex; align-items:center; gap:4px;">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </a>
                </div>
            @endif
        </div>

        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Nasabah (Siswa)</th>
                        <th>Kelas</th>
                        <th>Tipe</th>
                        <th>Kategori Sampah</th>
                        <th style="text-align:right;">Berat</th>
                        <th style="text-align:right;">Nominal Rupiah</th>
                        <th style="text-align:right;">Poin</th>
                        <th style="text-align:center;">Status</th>
                        <th>Operator</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td style="font-weight:700; font-family:var(--font-mono);">#{{ $tx->id }}</td>
                            <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td style="font-weight:700;">{{ $tx->student->name ?? 'N/A' }}</td>
                            <td>{{ $tx->student->class ?? 'N/A' }}</td>
                            <td>
                                @if($tx->type === 'setor')
                                    <span class="badge badge-success" style="background:var(--success-container); color:var(--success); border:1px solid var(--success); font-size:9.5px; padding:2px 6px;">Setor</span>
                                @else
                                    <span class="badge badge-danger" style="background:var(--danger-container); color:var(--danger); border:1px solid var(--danger); font-size:9.5px; padding:2px 6px;">Tarik</span>
                                @endif
                            </td>
                            <td>
                                @if($tx->type === 'setor')
                                    <span style="font-size:14px; margin-right:2px; display:inline-flex; align-items:center; justify-content:center; vertical-align:middle;">
                                        @if($tx->wasteCategory && (Str::startsWith($tx->wasteCategory->icon, '/uploads/') || Str::startsWith($tx->wasteCategory->icon, 'http')))
                                            <img src="{{ $tx->wasteCategory->icon }}" alt="{{ $tx->wasteCategory->name }}" style="width: 18px; height: 18px; object-fit: cover; border-radius: 2px; margin-right:4px;">
                                        @else
                                            {{ $tx->wasteCategory->icon ?? '🥤' }}
                                        @endif
                                    </span>
                                    {{ $tx->wasteCategory->name ?? 'Sampah' }}
                                @else
                                    <span style="color:var(--on-surface-variant); font-style:italic;">Tarik Tunai</span>
                                @endif
                            </td>
                            <td style="text-align:right; font-family:var(--font-mono);">
                                {{ $tx->weight ? number_format($tx->weight, 2, ',', '.') . ' kg' : '-' }}
                            </td>
                            <td style="text-align:right; font-family:var(--font-mono); font-weight:700; color:{{ $tx->type === 'setor' ? 'var(--success)' : 'var(--danger)' }}">
                                {{ $tx->type === 'setor' ? '+' : '−' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right; font-family:var(--font-mono); font-weight:700; color:var(--accent);">
                                {{ $tx->points ? '+' . number_format($tx->points, 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ strtolower($tx->status) }}" style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:3px;">{{ $tx->status }}</span>
                            </td>
                            <td>{{ $tx->operator->name ?? 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align:center; padding:var(--s-32); color:var(--on-surface-variant);">
                                <i class="bi bi-inbox" style="font-size:24px; display:block; margin-bottom:8px; color:var(--outline);"></i>
                                Tidak ditemukan data transaksi yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
