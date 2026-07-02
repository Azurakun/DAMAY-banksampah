@extends('layouts.app')
@section('title', 'Riwayat Transaksi — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    <a href="{{ route('siswa.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Beranda
    </a>

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-title">Riwayat Transaksi</h1>
            <p class="page-subtitle">Daur ulang &amp; tabungan Anda</p>
        </div>
    </div>

    {{-- Summary Stats (top) --}}
    @php
        $totalSetor = $transactions->where('type','setor')->sum('amount');
        $totalTarik = $transactions->where('type','tarik')->where('status','berhasil')->sum('amount');
        $totalBerat = $transactions->where('type','setor')->sum('weight');
    @endphp

    <div class="operator-stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:var(--s-20);">
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-green"><i class="bi bi-recycle"></i></div>
            <div class="stat-item-label">Total Setor</div>
            <div class="stat-item-value" style="font-size:16px;">Rp {{ number_format($totalSetor, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-amber"><i class="bi bi-cash"></i></div>
            <div class="stat-item-label">Total Tarik</div>
            <div class="stat-item-value" style="font-size:16px;">Rp {{ number_format($totalTarik, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-teal"><i class="bi bi-bag-check"></i></div>
            <div class="stat-item-label">Total Berat</div>
            <div class="stat-item-value" style="font-size:16px;">{{ number_format($totalBerat, 1, ',', '.') }} kg</div>
        </div>
    </div>

    {{-- Transaction List --}}
    <div class="transaction-list">
        @forelse($transactions as $tx)
            <div class="transaction-item">
                <div class="transaction-info">
                    <div class="transaction-icon-badge {{ $tx->type }}">
                        @if($tx->type === 'setor')
                            <i class="bi bi-arrow-down-left-circle" style="font-size:18px;"></i>
                        @else
                            <i class="bi bi-arrow-up-right-circle" style="font-size:18px;"></i>
                        @endif
                    </div>
                    <div class="transaction-details">
                        <span class="transaction-type-title">
                            {{ $tx->type === 'setor' ? 'Setor ' . ($tx->wasteCategory->name ?? 'Sampah') : 'Tarik Saldo Tabungan' }}
                        </span>
                        <span class="transaction-date">{{ $tx->created_at->format('d F Y, H:i') }}</span>
                        @if($tx->type === 'setor')
                            <span class="transaction-weight-tag">
                                <i class="bi bi-recycle" style="font-size:10px;color:var(--primary);"></i>
                                {{ number_format($tx->weight, 2, ',', '.') }} kg &nbsp;·&nbsp;
                                <i class="bi bi-star" style="font-size:10px;color:var(--accent);"></i>
                                +{{ $tx->points }} Poin
                            </span>
                        @endif
                        <span class="transaction-date">Oleh: {{ $tx->operator->name ?? 'Sistem' }}</span>
                        @if($tx->note)
                            <span class="transaction-date" style="font-style:italic;">"{{ $tx->note }}"</span>
                        @endif
                    </div>
                </div>
                <div class="transaction-value">
                    <span class="transaction-cash {{ $tx->type }}">
                        {{ $tx->type === 'setor' ? '+' : '−' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </span>
                    <span class="transaction-status {{ strtolower($tx->status) }}">{{ $tx->status }}</span>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="empty-state">
                    <i class="bi bi-inbox empty-icon"></i>
                    <h3 class="empty-title">Belum Ada Riwayat</h3>
                    <p class="empty-desc">Seluruh transaksi setoran sampah dan penarikan tunai Anda akan tampil di sini.</p>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection
