@extends('layouts.app')
@section('title', 'Setoran Berhasil — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="max-width:520px;margin:0 auto;">

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

        <div style="text-align:center;margin-top:var(--s-16);padding-top:var(--s-12);border-top:1px dashed var(--outline-variant);font-size:11px;color:var(--on-surface-variant);">
            <p>Terima kasih telah menjaga lingkungan! ♻️</p>
            <p style="font-weight:800;margin-top:2px;color:var(--primary);">#SMK2IndramayuGoGreen</p>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--s-8);">
        <button class="btn btn-ghost" onclick="window.print()" style="height:44px;font-size:13px;">
            <i class="bi bi-printer"></i> Cetak Struk
        </button>
        <a href="{{ route('operator.dashboard') }}" class="btn btn-primary" style="height:44px;font-size:13px;">
            <i class="bi bi-house-door"></i> Kembali
        </a>
    </div>

</div>
@endsection
