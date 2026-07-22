@extends('layouts.app')
@section('title', 'Setoran Berhasil — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="max-width:560px;margin:0 auto;">

    {{-- Success Banner --}}
    <div class="alert alert-success" style="margin-bottom:var(--s-20);">
        <span class="alert-icon"><i class="bi bi-check-circle"></i></span>
        <span class="alert-message">Setoran timbangan berhasil dicatat &amp; saldo siswa diperbarui!</span>
    </div>

    {{-- Receipt Slip --}}
    <div class="receipt-wrapper" id="receiptBlock">
        <div class="receipt-header">
            <div class="receipt-logo">
                <i class="bi bi-recycle" style="color:var(--primary);"></i>
            </div>
            <h2 class="receipt-title">EcoBank SMKN 2 Indramayu</h2>
            <p style="font-size:11px;margin-top:2px;color:var(--on-surface-variant);">Bank Sampah &amp; Tabungan Digital</p>
            <p style="font-size:10px;color:var(--on-surface-variant);">Jl. Raya Indramayu No.2, Jawa Barat</p>
        </div>

        {{-- Multi-transaction batch receipt --}}
        @if(isset($transactions) && $transactions->count() > 0)
            {{-- Header Info (shared by all transactions in this batch) --}}
            @php $firstTx = $transactions->first(); @endphp

            <div class="receipt-row">
                <span>Tanggal / Waktu:</span>
                <strong>{{ $firstTx->created_at->format('d/m/Y H:i') }}</strong>
            </div>
            <div class="receipt-row">
                <span>Siswa (Nasabah):</span>
                <strong>{{ $student->name }}</strong>
            </div>
            <div class="receipt-row">
                <span>Kelas / NISN:</span>
                <strong>{{ $student->class }} / {{ $student->nisn }}</strong>
            </div>
            <div class="receipt-row" style="border-bottom:1px dashed var(--outline-variant);padding-bottom:var(--s-8);margin-bottom:var(--s-8);">
                <span>Operator Tugas:</span>
                <strong>{{ $firstTx->operator->name }}</strong>
            </div>

            {{-- Line items per category --}}
            <div style="margin-bottom:var(--s-8);">
                <div style="font-size:10.5px; font-weight:700; color:var(--on-surface-variant); text-transform:uppercase; letter-spacing:.04em; margin-bottom:var(--s-8);">
                    Rincian Kategori Sampah
                </div>
                @foreach($transactions as $tx)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:var(--s-8) var(--s-10); background:var(--surface-container); border-radius:var(--r-md); margin-bottom:var(--s-6); gap:var(--s-8);">
                        <div>
                            <div style="font-size:12.5px; font-weight:700; color:var(--on-surface);">{{ $tx->wasteCategory->name }}</div>
                            <div style="font-size:10.5px; color:var(--on-surface-variant);">{{ number_format($tx->weight, 2, ',', '.') }} kg × Rp {{ number_format($tx->wasteCategory->price_per_kg, 0, ',', '.') }}/kg</div>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-size:13px; font-weight:800; color:var(--primary);">Rp {{ number_format($tx->amount, 0, ',', '.') }}</div>
                            <div style="font-size:10.5px; color:var(--accent);">⭐ +{{ number_format($tx->points, 0, ',', '.') }} poin</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="receipt-row" style="border-top:1px dashed var(--outline-variant); padding-top:var(--s-8); margin-top:var(--s-4);">
                <span>Total Berat:</span>
                <strong>{{ number_format($totalWeight, 2, ',', '.') }} kg</strong>
            </div>
            <div class="receipt-row">
                <span>Total Poin Diperoleh:</span>
                <strong style="color:var(--accent);">⭐ +{{ number_format($totalPoints, 0, ',', '.') }} Poin</strong>
            </div>
            <div class="receipt-total">
                <span>TOTAL TABUNGAN:</span>
                <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>

        @elseif(isset($transaction))
            {{-- Legacy single-transaction receipt --}}
            <div class="receipt-row">
                <span>No. Transaksi:</span>
                <strong>#TX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</strong>
            </div>
            <div class="receipt-row">
                <span>Tanggal / Waktu:</span>
                <strong>{{ $transaction->created_at->format('d/m/Y H:i') }}</strong>
            </div>
            <div class="receipt-row">
                <span>Siswa (Nasabah):</span>
                <strong>{{ $transaction->student->name }}</strong>
            </div>
            <div class="receipt-row">
                <span>Kelas / NISN:</span>
                <strong>{{ $transaction->student->class }} / {{ $transaction->student->nisn }}</strong>
            </div>
            <div class="receipt-row" style="border-bottom:1px dashed var(--outline-variant);padding-bottom:var(--s-8);margin-bottom:var(--s-8);">
                <span>Operator Tugas:</span>
                <strong>{{ $transaction->operator->name }}</strong>
            </div>
            <div class="receipt-row">
                <span>Kategori Sampah:</span>
                <strong>{{ $transaction->wasteCategory->name }}</strong>
            </div>
            <div class="receipt-row">
                <span>Berat Timbangan:</span>
                <strong>{{ number_format($transaction->weight, 2, ',', '.') }} kg</strong>
            </div>
            <div class="receipt-row">
                <span>Harga /kg:</span>
                <strong>Rp {{ number_format($transaction->wasteCategory->price_per_kg, 0, ',', '.') }}</strong>
            </div>
            <div class="receipt-row">
                <span>Poin Diperoleh:</span>
                <strong style="color:var(--accent);">⭐ +{{ number_format($transaction->points, 0, ',', '.') }} Poin</strong>
            </div>
            <div class="receipt-total">
                <span>TOTAL TABUNGAN:</span>
                <span>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>
        @endif

        <div style="text-align:center;margin-top:var(--s-16);padding-top:var(--s-12);border-top:1px dashed var(--outline-variant);font-size:11px;color:var(--on-surface-variant);">
            <p>Terima kasih telah menjaga lingkungan! ♻️</p>
            <p style="font-weight:800;margin-top:2px;color:var(--primary);">#SMK2IndramayuGoGreen</p>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--s-8);margin-top:var(--s-16);">
        <button class="btn btn-ghost" onclick="window.print()" style="height:44px;font-size:13px;">
            <i class="bi bi-printer"></i> Cetak Struk
        </button>
        <a href="{{ route('operator.dashboard') }}" class="btn btn-primary" style="height:44px;font-size:13px;">
            <i class="bi bi-house-door"></i> Kembali
        </a>
    </div>

</div>
@endsection
