@extends('layouts.app')
@section('title', 'Struk #TX-{{ str_pad($transaction->id, 6, "0", STR_PAD_LEFT) }} — EcoBank SMKN 2 Indramayu')

@section('content')
@php $statusLower = strtolower($transaction->status); @endphp

<style>
/* ═══════════════════════════════════════════
   SCREEN VIEW — Modern card preview
═══════════════════════════════════════════ */
.rx-page {
    max-width: 400px;
    margin: 0 auto;
}
.rx-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 16px;
}
.rx-card {
    background: var(--surface);
    border: 1.5px solid var(--outline-variant);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 24px rgba(0,0,0,0.10);
    margin-bottom: 12px;
}
/* Thermal-style font everywhere in the receipt */
.rx-body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 13px;
    padding: 16px;
    color: #111;
}
.rx-header-block {
    background: linear-gradient(135deg, #1b4d2b, #2e7d32);
    padding: 20px 16px 16px;
    text-align: center;
    color: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.rx-header-block .rx-store-icon { font-size: 28px; display:block; margin-bottom:4px; }
.rx-header-block .rx-store-name { font-size: 15px; font-weight: 800; letter-spacing: 0.3px; font-family: sans-serif; }
.rx-header-block .rx-store-sub  { font-size: 9.5px; color: rgba(255,255,255,0.80); margin-top: 2px; font-family: sans-serif; }
.rx-header-block .rx-type-badge {
    display: inline-block;
    margin-top: 10px;
    padding: 3px 12px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-family: sans-serif;
}
.rx-type-badge.berhasil { background: rgba(255,255,255,0.95); color: #1b4d2b; }
.rx-type-badge.menunggu { background: rgba(255,215,0,0.95); color: #5a3a00; }
.rx-type-badge.batal    { background: rgba(220,60,40,0.95); color: #fff; }

/* Thermal row: label left | value right */
.rx-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 2px 0;
    gap: 6px;
    font-size: 12px;
    line-height: 1.5;
}
.rx-row .rx-label { color: #555; flex-shrink: 0; font-size: 11px; }
.rx-row .rx-val   { text-align: right; font-weight: 700; word-break: break-word; }
.rx-div { border-top: 1px dashed #aaa; margin: 8px 0; }
.rx-div-solid { border-top: 1.5px solid #888; margin: 8px 0; }
.rx-section-title {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #777;
    margin: 10px 0 4px;
    font-family: sans-serif;
}

/* ITEM LINE in thermal style */
.rx-item-block {
    margin: 4px 0;
    border-bottom: 1px dashed #ccc;
    padding-bottom: 6px;
}
.rx-item-name  { font-weight: 700; font-size: 12.5px; }
.rx-item-calc  { display: flex; justify-content: space-between; font-size: 11.5px; color: #444; margin-top: 1px; }
.rx-item-total { font-weight: 800; }

/* TOTAL LINE */
.rx-total-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    padding: 8px 10px;
    background: rgba(30, 100, 50, 0.07);
    border: 1.5px solid #2e7d32;
    border-radius: 6px;
    font-weight: 800;
    font-size: 15px;
}
.rx-total-line .rx-total-label { font-size: 10px; color: #2e7d32; text-transform:uppercase; letter-spacing:.5px; font-family:sans-serif; }
.rx-total-line .rx-total-val   { color: #2e7d32; font-size: 16px; }

/* POINTS BOX */
.rx-points-line {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 6px; padding: 6px 10px;
    background: rgba(255,180,0,0.08); border: 1px dashed #d4a000;
    border-radius: 5px; font-size: 12px;
}

.rx-footer {
    text-align: center;
    margin-top: 14px;
    padding-top: 10px;
    border-top: 1px dashed #aaa;
    font-size: 10.5px;
    color: #666;
    line-height: 1.8;
    font-family: sans-serif;
}
.rx-footer strong { display: block; color: #2e7d32; font-size: 11.5px; }

/* Cut line */
.rx-cut {
    display: flex; align-items: center; gap: 6px;
    color: #bbb; font-size: 10.5px; margin: 4px 0 8px;
    font-family: sans-serif;
}
.rx-cut::before, .rx-cut::after { content:''; flex:1; border-top:1px dashed #ccc; }


/* ═══════════════════════════════════════════
   PRINT MODE — True thermal paper output
   Target: 80mm thermal paper
   Uses visibility override (NOT display:none on body)
   so nested elements can override the global
   body * { visibility: hidden } rule in app.blade.php
═══════════════════════════════════════════ */
@media print {
    /* Paper size: 80mm wide, auto height */
    @page {
        size: 80mm auto;
        margin: 0;
    }

    /* Make the thermal zone visible and position it top-left on paper */
    .rx-thermal-print,
    .rx-thermal-print * { visibility: visible !important; }

    .rx-thermal-print {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 76mm !important;
        margin: 0 !important;
        padding: 2mm 2mm !important;
        background: #fff !important;
    }

    /* Strip card chrome for cleaner print */
    .rx-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; overflow: visible !important; }
    .rx-body { padding: 3mm 1mm !important; font-size: 9.5pt !important; }
    .rx-row  { font-size: 9.5pt !important; }
    .rx-item-name { font-size: 10pt !important; }
    .rx-item-calc { font-size: 9pt !important; }

    /* Keep header background for thermal printers that support color */
    .rx-header-block {
        background: #1b4d2b !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        padding: 5mm 3mm 4mm !important;
    }

    /* Total box — dark border for B&W printers */
    .rx-total-line {
        border: 2px solid #000 !important;
        background: #eeeeee !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .rx-total-line .rx-total-label { color: #000 !important; }
    .rx-total-line .rx-total-val   { color: #000 !important; }

    /* Points box */
    .rx-points-line {
        border: 1px dashed #555 !important;
        background: #f5f5f5 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<div class="rx-page animate-fade-in">

    {{-- Screen back link --}}
    <a href="{{ $backUrl }}" class="back-link"><i class="bi bi-arrow-left"></i> Kembali</a>

    {{-- ══════════════════════════════════
         THERMAL PRINT ZONE
         This div is what gets sent to the printer
    ═══════════════════════════════════ --}}
    <div class="rx-thermal-print">
        <div class="rx-card">

            {{-- ── HEADER ── --}}
            <div class="rx-header-block">
                <i class="bi bi-recycle rx-store-icon"></i>
                <div class="rx-store-name">EcoBank SMKN 2 Indramayu</div>
                <div class="rx-store-sub">Bank Sampah &amp; Tabungan Digital</div>
                <div class="rx-store-sub">Jl. Raya Cimanuk No.2, Indramayu</div>
                <div class="rx-store-sub">Telp: (0234) 000-0000</div>
                <div class="rx-type-badge {{ $statusLower }}">
                    @if($statusLower === 'berhasil') ✔ Berhasil
                    @elseif($statusLower === 'menunggu') ⏳ Menunggu
                    @else ✘ Dibatalkan
                    @endif
                </div>
            </div>

            <div class="rx-body">

                {{-- ── TX INFO ── --}}
                <div class="rx-div-solid"></div>
                <div class="rx-row">
                    <span class="rx-label">No. Transaksi</span>
                    <span class="rx-val">#TX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="rx-row">
                    <span class="rx-label">Tanggal</span>
                    <span class="rx-val">{{ $transaction->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="rx-row">
                    <span class="rx-label">Pukul</span>
                    <span class="rx-val">{{ $transaction->created_at->format('H:i') }} WIB</span>
                </div>
                <div class="rx-row">
                    <span class="rx-label">Jenis</span>
                    <span class="rx-val">
                        @if($transaction->type === 'setor') Setoran Sampah
                        @elseif($transaction->type === 'tarik') Penarikan Saldo
                        @else {{ ucfirst($transaction->type) }}
                        @endif
                    </span>
                </div>

                {{-- ── NASABAH ── --}}
                <div class="rx-div"></div>
                <div class="rx-section-title">Data Nasabah</div>
                <div class="rx-row">
                    <span class="rx-label">Nama</span>
                    <span class="rx-val">{{ $transaction->student->name }}</span>
                </div>
                <div class="rx-row">
                    <span class="rx-label">Kelas</span>
                    <span class="rx-val">{{ $transaction->student->class ?? '-' }}</span>
                </div>
                <div class="rx-row">
                    <span class="rx-label">NISN</span>
                    <span class="rx-val">{{ $transaction->student->nisn ?? '-' }}</span>
                </div>
                <div class="rx-row">
                    <span class="rx-label">Operator</span>
                    <span class="rx-val">{{ $transaction->operator->name ?? 'Sistem' }}</span>
                </div>

                {{-- ── DETAIL ── --}}
                <div class="rx-div"></div>

                @if($transaction->type === 'setor')
                    <div class="rx-section-title">Detail Setoran Sampah</div>
                    <div class="rx-item-block">
                        <div class="rx-item-name">{{ $transaction->wasteCategory->name ?? 'Sampah Umum' }}</div>
                        <div class="rx-item-calc">
                            <span>{{ number_format($transaction->weight, 2, ',', '.') }} kg
                                × Rp {{ number_format($transaction->wasteCategory->price_per_kg ?? 0, 0, ',', '.') }}/kg</span>
                            <span class="rx-item-total">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="rx-div-solid"></div>
                    <div class="rx-total-line">
                        <span class="rx-total-label">Total Tabungan</span>
                        <span class="rx-total-val">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>

                    {{-- Points --}}
                    @if($transaction->points > 0)
                        <div class="rx-points-line">
                            <span style="font-weight:700; color:#a07000;">★ Poin Diperoleh</span>
                            <strong style="color:#a07000; font-size:14px;">+{{ number_format($transaction->points, 0, ',', '.') }} Poin</strong>
                        </div>
                    @endif

                    @if($transaction->note)
                        <div class="rx-div"></div>
                        <div class="rx-row" style="font-size:10.5px; color:#666; font-style:italic;">
                            <span class="rx-label">Catatan</span>
                            <span class="rx-val">{{ $transaction->note }}</span>
                        </div>
                    @endif

                @elseif($transaction->type === 'tarik')
                    <div class="rx-section-title">Detail Penarikan</div>
                    <div class="rx-row">
                        <span class="rx-label">Nominal</span>
                        <span class="rx-val" style="font-size:14px; font-weight:800;">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>
                    @if($transaction->note)
                        <div class="rx-row" style="font-size:10.5px;">
                            <span class="rx-label">Keterangan</span>
                            <span class="rx-val">{{ $transaction->note }}</span>
                        </div>
                    @endif
                    <div class="rx-div-solid"></div>
                    <div class="rx-total-line"
                         style="{{ $statusLower === 'berhasil' ? 'border-color:#c00;' : '' }}">
                        <span class="rx-total-label" style="{{ $statusLower === 'berhasil' ? 'color:#c00;' : '' }}">Total Ditarik</span>
                        <span class="rx-total-val" style="{{ $statusLower === 'berhasil' ? 'color:#c00;' : '' }}">
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </span>
                    </div>

                @else
                    <div class="rx-section-title">Detail Transaksi</div>
                    <div class="rx-row">
                        <span class="rx-label">Jumlah</span>
                        <span class="rx-val">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>
                    @if($transaction->note)
                        <div class="rx-row"><span class="rx-label">Catatan</span><span class="rx-val">{{ $transaction->note }}</span></div>
                    @endif
                    <div class="rx-div-solid"></div>
                    <div class="rx-total-line">
                        <span class="rx-total-label">Total</span>
                        <span class="rx-total-val">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>
                @endif

                {{-- ── FOOTER ── --}}
                <div class="rx-footer">
                    <div>Terima kasih telah menjaga lingkungan!</div>
                    <div>Struk ini adalah bukti transaksi resmi.</div>
                    <strong>#SMK2IndramayuGoGreen</strong>
                </div>

            </div>{{-- /rx-body --}}
        </div>{{-- /rx-card --}}

        {{-- Cut line --}}
        <div class="rx-cut"><i class="bi bi-scissors"></i> potong di sini</div>
    </div>{{-- /rx-thermal-print --}}

    {{-- ── ACTION BUTTONS (Screen only) ── --}}
    <div class="rx-actions">
        <button onclick="window.print()" class="btn btn-primary"
                style="height:44px; font-size:13px; display:flex; align-items:center; justify-content:center; gap:6px;">
            <i class="bi bi-printer-fill"></i> Cetak Struk
        </button>
        <a href="{{ $backUrl }}" class="btn btn-ghost"
           style="height:44px; font-size:13px; display:flex; align-items:center; justify-content:center; gap:6px;">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

</div>
@endsection
