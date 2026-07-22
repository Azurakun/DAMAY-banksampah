@extends('layouts.app')
@section('title', 'Detail Distribusi #' . $distribution->id . ' — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Breadcrumb --}}
    <div style="margin-bottom: var(--s-24); display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:var(--s-12);">
        <div>
            <a href="{{ route(Auth::user()->role . '.distributions.index') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:var(--primary); text-decoration:none; margin-bottom: 8px;">
                <i class="bi bi-arrow-left"></i> Kembali ke Riwayat Distribusi
            </a>
            <h2 style="font-family:var(--font-display); font-size:26px; font-weight:800; color:var(--primary); margin-top:4px;">Detail Batch Distribusi #{{ $distribution->id }}</h2>
        </div>
        <a href="{{ route(Auth::user()->role . '.distributions.receipt', $distribution->id) }}"
           class="btn btn-primary" style="height:42px; font-size:13px; display:inline-flex; align-items:center; gap:6px; padding:0 18px; flex-shrink:0;">
            <i class="bi bi-printer-fill"></i> Cetak Struk / Nota
        </a>
    </div>

    <style>
        .distribution-details-grid {
            display: grid;
            grid-template-columns: 1fr 1.8fr;
            gap: var(--s-20);
            align-items: start;
        }
        @media (max-width: 768px) {
            .distribution-details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- Info Card --}}
    <div class="distribution-details-grid">
        
        {{-- Summary Column --}}
        <div class="card" style="border-top: 5px solid var(--accent); padding: var(--s-24);">
            <h3 style="font-family:var(--font-display); font-size:16px; font-weight:800; color:var(--primary); margin-bottom:var(--s-16); border-bottom:1px dashed var(--outline); padding-bottom:8px;">
                Ringkasan Distribusi
            </h3>

            <div style="display:flex; flex-direction:column; gap:var(--s-12); font-size:13.5px;">
                <div>
                    <span style="color:var(--on-surface-variant); font-weight:600; display:block; font-size:11px; text-transform:uppercase;">Tanggal Distribusi</span>
                    <strong style="color:var(--on-surface);">{{ \Carbon\Carbon::parse($distribution->batch_date)->translatedFormat('d F Y') }}</strong>
                </div>

                <div>
                    <span style="color:var(--on-surface-variant); font-weight:600; display:block; font-size:11px; text-transform:uppercase;">Jalur Distribusi</span>
                    @if($distribution->route === 'agent')
                        <span class="badge badge-success" style="background:var(--success-container); color:var(--success); border:1px solid var(--success); font-size:11px; margin-top:2px;">
                            <i class="bi bi-shop"></i> Jual ke Agen
                        </span>
                    @else
                        <span class="badge badge-teal" style="background:var(--teal-container); color:var(--teal); border:1px solid var(--teal); font-size:11px; margin-top:2px;">
                            <i class="bi bi-recycle"></i> Unit Internal
                        </span>
                    @endif
                </div>

                <div>
                    <span style="color:var(--on-surface-variant); font-weight:600; display:block; font-size:11px; text-transform:uppercase;">Nama Agen / Unit</span>
                    <strong style="color:var(--on-surface);">{{ $distribution->agent_name ?: '-' }}</strong>
                </div>

                <div>
                    <span style="color:var(--on-surface-variant); font-weight:600; display:block; font-size:11px; text-transform:uppercase;">Dicatat Oleh</span>
                    <strong style="color:var(--on-surface);">{{ $distribution->creator->name ?? 'N/A' }}</strong>
                </div>

                <div>
                    <span style="color:var(--on-surface-variant); font-weight:600; display:block; font-size:11px; text-transform:uppercase;">Waktu Pencatatan</span>
                    <strong style="color:var(--on-surface);">{{ $distribution->created_at->format('d/m/Y H:i') }}</strong>
                </div>

                @if($distribution->notes)
                    <div style="border-top: 1px dashed var(--outline); padding-top: 10px; margin-top: 4px;">
                        <span style="color:var(--on-surface-variant); font-weight:600; display:block; font-size:11px; text-transform:uppercase;">Catatan</span>
                        <p style="color:var(--on-surface-variant); line-height:1.4; margin-top:4px;">{{ $distribution->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Details Column --}}
        <div class="card" style="border-top: 5px solid var(--primary); padding: var(--s-24);">
            <h3 style="font-family:var(--font-display); font-size:16px; font-weight:800; color:var(--primary); margin-bottom:var(--s-16); border-bottom:1px dashed var(--outline); padding-bottom:8px;">
                Rincian Item Sampah
            </h3>

            <div class="table-overflow">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kategori Sampah</th>
                            <th style="text-align:right;">Timbangan (Berat)</th>
                            @if($distribution->route === 'agent')
                                <th style="text-align:right;">Harga Jual</th>
                                <th style="text-align:right;">Subtotal</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($distribution->items as $item)
                            <tr>
                                <td style="font-weight:700; display:flex; align-items:center; gap:6px;">
                                    <span style="font-size:16px; display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; overflow:hidden;">
                                        @if(isset($item->wasteCategory->icon) && (Str::startsWith($item->wasteCategory->icon, '/uploads/') || Str::startsWith($item->wasteCategory->icon, 'http')))
                                            <img src="{{ $item->wasteCategory->icon }}" alt="{{ $item->wasteCategory->name }}" style="width: 20px; height: 20px; object-fit: cover; border-radius: 3px;">
                                        @else
                                            {{ $item->wasteCategory->icon ?? '🍂' }}
                                        @endif
                                    </span>
                                    {{ $item->wasteCategory->name ?? 'N/A' }}
                                </td>
                                <td style="text-align:right; font-weight:700; font-family:var(--font-mono);">
                                    {{ number_format($item->weight, 2, ',', '.') }} kg
                                </td>
                                @if($distribution->route === 'agent')
                                    <td style="text-align:right; font-family:var(--font-mono);">
                                        Rp {{ number_format($item->price_per_kg, 0, ',', '.') }} /kg
                                    </td>
                                    <td style="text-align:right; font-weight:700; font-family:var(--font-mono); color:var(--success);">
                                        Rp {{ number_format($item->value, 0, ',', '.') }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary Totals --}}
            <div style="margin-top: var(--s-20); border-top: 2px dashed var(--outline); padding-top: var(--s-16); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:11px; font-weight:600; color:var(--on-surface-variant); text-transform:uppercase;">Akumulasi Berat</span>
                    <div style="font-family:var(--font-mono); font-size:16px; font-weight:800; color:var(--primary);">
                        {{ number_format($distribution->total_weight, 2, ',', '.') }} kg
                    </div>
                </div>
                
                @if($distribution->route === 'agent')
                    <div style="text-align:right;">
                        <span style="font-size:11px; font-weight:600; color:var(--on-surface-variant); text-transform:uppercase;">Pemasukan Kas Masuk</span>
                        <div style="font-family:var(--font-mono); font-size:22px; font-weight:800; color:var(--accent);">
                            Rp {{ number_format($distribution->total_value, 0, ',', '.') }}
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
