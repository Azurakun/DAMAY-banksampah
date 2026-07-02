@extends('layouts.app')
@section('title', 'Riwayat Transaksi Operator — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    <a href="{{ route('operator.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Dasbor
    </a>

    <div class="page-header">
        <div class="page-header-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-title">Riwayat Transaksi</h1>
            <p class="page-subtitle">Semua transaksi yang Anda proses</p>
        </div>
    </div>

    {{-- Summary Stats --}}
    @php
        $totSetor  = $transactions->where('type','setor')->sum('amount');
        $totTarik  = $transactions->where('type','tarik')->where('status','berhasil')->sum('amount');
        $totBerat  = $transactions->where('type','setor')->sum('weight');
    @endphp

    <div class="operator-stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:var(--s-20);">
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-green"><i class="bi bi-recycle"></i></div>
            <div class="stat-item-label">Total Setoran</div>
            <div class="stat-item-value" style="font-size:15px;">Rp {{ number_format($totSetor, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-amber"><i class="bi bi-cash"></i></div>
            <div class="stat-item-label">Total Tarik</div>
            <div class="stat-item-value" style="font-size:15px;">Rp {{ number_format($totTarik, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-teal"><i class="bi bi-bag-check"></i></div>
            <div class="stat-item-label">Total Berat</div>
            <div class="stat-item-value" style="font-size:15px;">{{ number_format($totBerat, 1, ',', '.') }} kg</div>
        </div>
    </div>

    {{-- Transaction list --}}
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
                            {{ $tx->type === 'setor' ? 'Setoran ' . ($tx->wasteCategory->name ?? 'Sampah') : 'Penarikan Tunai' }}
                        </span>
                        <span class="transaction-date">
                            Nasabah: <strong>{{ $tx->student->name }}</strong> ({{ $tx->student->class }})
                        </span>
                        <span class="transaction-date">{{ $tx->created_at->format('d/m/Y H:i') }}</span>
                        @if($tx->type === 'setor')
                            <span class="transaction-weight-tag">
                                <i class="bi bi-recycle" style="font-size:10px;color:var(--primary);"></i>
                                {{ number_format($tx->weight, 2, ',', '.') }} kg · +{{ $tx->points }} Poin
                            </span>
                        @endif
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
                    @if($tx->type === 'setor')
                        <a href="{{ route('operator.confirm', $tx->id) }}" style="font-size:11px;color:var(--primary);text-decoration:none;font-weight:700;margin-top:2px;">
                            <i class="bi bi-receipt"></i> Struk
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="card">
                <div class="empty-state">
                    <i class="bi bi-folder empty-icon"></i>
                    <h3 class="empty-title">Belum Ada Riwayat</h3>
                    <p class="empty-desc">Transaksi yang Anda proses akan tampil di sini.</p>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection
