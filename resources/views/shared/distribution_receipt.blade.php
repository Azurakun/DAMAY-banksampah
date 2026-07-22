@extends('layouts.app')
@section('title', 'Nota Distribusi #DIST-{{ str_pad($distribution->id, 5, "0", STR_PAD_LEFT) }} — EcoBank SMKN 2 Indramayu')

@section('content')

<style>
/* ═══════════════════════════════════════════
   SCREEN VIEW — Modern card preview
═══════════════════════════════════════════ */
.drx-page {
    max-width: 440px;
    margin: 0 auto;
}
.drx-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 16px;
}
.drx-card {
    background: var(--surface);
    border: 1.5px solid var(--outline-variant);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 24px rgba(0,0,0,0.10);
    margin-bottom: 12px;
}
.drx-header-block {
    background: linear-gradient(135deg, #0d2b1a, #1b5e20);
    padding: 20px 16px 16px;
    text-align: center;
    color: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.drx-header-block .drx-icon { font-size:28px; display:block; margin-bottom:4px; }
.drx-header-block .drx-name { font-size:15px; font-weight:800; font-family:sans-serif; letter-spacing:.3px; }
.drx-header-block .drx-sub  { font-size:9.5px; color:rgba(255,255,255,.78); margin-top:2px; font-family:sans-serif; }
.drx-header-block .drx-doc-label {
    display: inline-block;
    margin-top: 10px;
    padding: 3px 12px;
    border: 1px solid rgba(255,255,255,.45);
    border-radius: 4px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(255,255,255,.95);
    font-family: sans-serif;
}

.drx-body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 12.5px;
    padding: 16px;
    color: #111;
}

/* Info row: left label, right value */
.drx-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 2px 0;
    gap: 6px;
    font-size: 12px;
    line-height: 1.5;
}
.drx-row .drx-label { color: #555; flex-shrink: 0; font-size: 11px; min-width: 100px; }
.drx-row .drx-val   { text-align: right; font-weight: 700; word-break: break-word; }

.drx-div { border-top: 1px dashed #aaa; margin: 8px 0; }
.drx-div-solid { border-top: 1.5px solid #555; margin: 8px 0; }
.drx-section-title {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #777;
    margin: 10px 0 4px;
    font-family: sans-serif;
}

/* Item lines — no table, just stacked blocks for narrow paper */
.drx-item {
    margin: 5px 0;
    padding-bottom: 5px;
    border-bottom: 1px dashed #ccc;
}
.drx-item-name { font-weight: 700; font-size: 12.5px; margin-bottom: 1px; }
.drx-item-line {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #444;
}
.drx-item-line .drx-sub-val { font-weight: 800; }

/* Total summary rows */
.drx-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 3px 0;
    font-size: 12px;
}
.drx-summary-row.total-weight { color: #2e7d32; font-weight: 700; }

.drx-grand-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    padding: 8px 10px;
    background: rgba(30, 100, 50, 0.07);
    border: 1.5px solid #2e7d32;
    border-radius: 6px;
    font-weight: 800;
}
.drx-grand-total .drx-gt-label { font-size: 10px; color: #2e7d32; text-transform:uppercase; letter-spacing:.5px; font-family:sans-serif; }
.drx-grand-total .drx-gt-val   { font-size: 16px; color: #2e7d32; font-family: 'Courier New', monospace; }

/* Signature section */
.drx-sig-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 8px;
    text-align: center;
    font-size: 11px;
}
.drx-sig-label { color: #555; font-weight: 600; font-size: 10.5px; margin-bottom: 36px; font-family: sans-serif; }
.drx-sig-line { border-top: 1px solid #555; padding-top: 3px; font-weight: 700; font-size: 11px; }

.drx-footer {
    text-align: center;
    margin-top: 14px;
    padding-top: 10px;
    border-top: 1px dashed #aaa;
    font-size: 10px;
    color: #666;
    line-height: 1.8;
    font-family: sans-serif;
}
.drx-footer strong { display: block; color: #2e7d32; font-size: 11px; }

.drx-cut {
    display: flex; align-items: center; gap: 6px;
    color: #bbb; font-size: 10.5px; margin: 4px 0 8px;
    font-family: sans-serif;
}
.drx-cut::before, .drx-cut::after { content:''; flex:1; border-top:1px dashed #ccc; }

/* ═══════════════════════════════════════════
   PRINT MODE — 80mm thermal paper
   Uses visibility override (NOT display:none on body)
═══════════════════════════════════════════ */
@media print {
    @page {
        size: 80mm auto;
        margin: 0;
    }

    /* Hide everything first */
    body * { visibility: hidden; }

    /* Show ONLY the thermal print zone */
    .drx-thermal-print,
    .drx-thermal-print * { visibility: visible !important; }

    .drx-thermal-print {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 76mm !important;
        margin: 0 !important;
        padding: 2mm 2mm !important;
        background: #fff !important;
        font-family: 'Courier New', Courier, monospace !important;
        font-size: 9.5pt !important;
        color: #000 !important;
    }

    .drx-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; overflow: visible !important; }
    .drx-header-block {
        background: #0d2b1a !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        padding: 5mm 3mm 4mm !important;
    }
    .drx-body { padding: 2mm 1mm !important; font-size: 9.5pt !important; }
    .drx-row { font-size: 9.5pt !important; }
    .drx-item-name { font-size: 10pt !important; }
    .drx-item-line { font-size: 9pt !important; }

    .drx-grand-total {
        border: 2px solid #000 !important;
        background: #eeeeee !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .drx-grand-total .drx-gt-label { color: #000 !important; }
    .drx-grand-total .drx-gt-val   { color: #000 !important; }

    .drx-summary-row.total-weight { color: #000 !important; }
}
</style>

<div class="drx-page animate-fade-in">

    <a href="{{ $backUrl }}" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Riwayat Distribusi</a>

    {{-- ══════════════════════════════════
         THERMAL PRINT ZONE
    ═══════════════════════════════════ --}}
    <div class="drx-thermal-print">
        <div class="drx-card">

            {{-- HEADER --}}
            <div class="drx-header-block">
                <i class="bi bi-truck drx-icon"></i>
                <div class="drx-name">EcoBank SMKN 2 Indramayu</div>
                <div class="drx-sub">Bank Sampah &amp; Tabungan Digital Sekolah</div>
                <div class="drx-sub">Jl. Raya Cimanuk No.2, Indramayu, Jawa Barat</div>
                <div class="drx-doc-label">Surat Jalan / Nota Distribusi</div>
            </div>

            <div class="drx-body">

                {{-- BATCH INFO --}}
                <div class="drx-div-solid"></div>
                <div class="drx-row">
                    <span class="drx-label">No. Batch</span>
                    <span class="drx-val">#DIST-{{ str_pad($distribution->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="drx-row">
                    <span class="drx-label">Tgl. Distribusi</span>
                    <span class="drx-val">{{ \Carbon\Carbon::parse($distribution->batch_date)->format('d/m/Y') }}</span>
                </div>
                <div class="drx-row">
                    <span class="drx-label">Tgl. Cetak</span>
                    <span class="drx-val">{{ now()->format('d/m/Y H:i') }} WIB</span>
                </div>
                <div class="drx-row">
                    <span class="drx-label">Dicatat Oleh</span>
                    <span class="drx-val">{{ $distribution->creator->name ?? 'N/A' }}</span>
                </div>
                <div class="drx-row">
                    <span class="drx-label">Jalur</span>
                    <span class="drx-val">{{ $distribution->route === 'agent' ? 'Jual ke Agen' : 'Unit Internal' }}</span>
                </div>
                @if($distribution->agent_name)
                    <div class="drx-row">
                        <span class="drx-label">Nama Agen</span>
                        <span class="drx-val">{{ $distribution->agent_name }}</span>
                    </div>
                @endif
                @if($distribution->notes)
                    <div class="drx-row" style="font-style:italic; font-size:10.5px;">
                        <span class="drx-label">Catatan</span>
                        <span class="drx-val" style="font-weight:500;">{{ $distribution->notes }}</span>
                    </div>
                @endif

                {{-- ITEMS --}}
                <div class="drx-div"></div>
                <div class="drx-section-title">Rincian Barang Keluar</div>

                @php $no = 1; @endphp
                @foreach($distribution->items as $item)
                    <div class="drx-item">
                        <div class="drx-item-name">{{ $no++ }}. {{ $item->wasteCategory->name ?? '—' }}</div>
                        <div class="drx-item-line">
                            <span>Berat: {{ number_format($item->weight, 2, ',', '.') }} kg</span>
                            @if($distribution->route === 'agent')
                                <span>@ Rp {{ number_format($item->price_per_kg, 0, ',', '.') }}/kg</span>
                            @endif
                        </div>
                        @if($distribution->route === 'agent')
                            <div class="drx-item-line">
                                <span>Subtotal</span>
                                <span class="drx-sub-val">Rp {{ number_format($item->value, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- SUMMARY --}}
                <div class="drx-div-solid"></div>
                <div class="drx-summary-row total-weight">
                    <span>TOTAL BERAT</span>
                    <span>{{ number_format($distribution->total_weight, 2, ',', '.') }} kg</span>
                </div>

                @if($distribution->route === 'agent')
                    <div class="drx-grand-total">
                        <span class="drx-gt-label">Kas Masuk</span>
                        <span class="drx-gt-val">Rp {{ number_format($distribution->total_value, 0, ',', '.') }}</span>
                    </div>
                @endif

                {{-- SIGNATURES --}}
                <div class="drx-div" style="margin-top:16px;"></div>
                <div class="drx-section-title">Tanda Tangan</div>
                <div class="drx-sig-grid">
                    <div>
                        <div class="drx-sig-label">Petugas Bank Sampah</div>
                        <div class="drx-sig-line">{{ $distribution->creator->name ?? '_______________' }}</div>
                    </div>
                    <div>
                        <div class="drx-sig-label">Penerima / Agen</div>
                        <div class="drx-sig-line">{{ $distribution->agent_name ?: '_______________' }}</div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="drx-footer">
                    <div>Dokumen sah distribusi sampah keluar dari</div>
                    <div>Bank Sampah SMKN 2 Indramayu.</div>
                    <strong>#SMK2IndramayuGoGreen</strong>
                </div>

            </div>{{-- /drx-body --}}
        </div>{{-- /drx-card --}}

        <div class="drx-cut"><i class="bi bi-scissors"></i> potong di sini</div>
    </div>{{-- /drx-thermal-print --}}

    {{-- ACTION BUTTONS --}}
    <div class="drx-actions">
        <button onclick="window.print()" class="btn btn-primary"
                style="height:44px; font-size:13px; display:flex; align-items:center; justify-content:center; gap:6px;">
            <i class="bi bi-printer-fill"></i> Cetak Nota
        </button>
        <a href="{{ $backUrl }}" class="btn btn-ghost"
           style="height:44px; font-size:13px; display:flex; align-items:center; justify-content:center; gap:6px;">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

</div>
@endsection
