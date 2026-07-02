@extends('layouts.app')
@section('title', 'Beranda Siswa — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    {{-- Greeting Row --}}
    <div class="greeting-row">
        <a href="{{ route('siswa.profile') }}" class="greeting-avatar" style="text-decoration:none;">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
            @else
                <i class="bi bi-person" style="color:var(--on-primary);font-size:26px;"></i>
            @endif
        </a>
        <div>
            <div class="greeting-name">Halo, {{ explode(' ', $user->name)[0] }}! 👋</div>
            <div class="greeting-meta">
                <i class="bi bi-building" style="margin-right:3px;"></i>{{ $user->class }}
                &nbsp;·&nbsp;
                <i class="bi bi-credit-card" style="margin-right:3px;"></i>{{ $user->nisn }}
            </div>
        </div>
    </div>

    {{-- Dashboard Grid --}}
    <div class="dashboard-grid">
        
        {{-- Left Column: Balance & Quick Actions --}}
        <div class="dashboard-column-left">
            {{-- Balance Hero Card --}}
            <div class="balance-card">
                <div class="balance-card-inner">
                    <div class="balance-label">
                        <i class="bi bi-wallet2" style="margin-right:5px;"></i>Saldo Tabungan Sampah
                    </div>
                    <div class="balance-amount">Rp {{ number_format($user->balance, 0, ',', '.') }}</div>
                    <div class="balance-footer">
                        <div class="points-badge">
                            <i class="bi bi-star" style="font-size:12px;"></i>
                            {{ number_format($user->points, 0, ',', '.') }} Poin
                        </div>
                        <span class="balance-school">
                            <i class="bi bi-recycle"></i> SMKN 2 Indramayu
                        </span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Grid --}}
            <div class="action-grid">
                <a href="{{ route('siswa.history') }}" class="action-card">
                    <div class="action-icon"><i class="bi bi-clock-history"></i></div>
                    <span class="action-label">Riwayat</span>
                </a>
                <a href="{{ route('siswa.leaderboard') }}" class="action-card">
                    <div class="action-icon"><i class="bi bi-trophy"></i></div>
                    <span class="action-label">Peringkat</span>
                </a>
                <a href="{{ route('siswa.withdraw') }}" class="action-card" style="grid-column:span 2;">
                    <div class="action-icon" style="width:56px;height:56px;">
                        <i class="bi bi-cash-coin" style="font-size:26px;"></i>
                    </div>
                    <span class="action-label">Tarik Saldo Tabungan</span>
                </a>
            </div>
        </div>

        {{-- Right Column: Eco Impact & Recent Transactions --}}
        <div class="dashboard-column-right">
            {{-- Environmental Impact Card --}}
            <div class="eco-stats-card">
                <div class="flex-between" style="margin-bottom:var(--s-6);">
                    <div style="display:flex;align-items:center;gap:var(--s-6);">
                        <i class="bi bi-recycle" style="color:var(--primary);font-size:16px;"></i>
                        <span style="font-size:14px;font-weight:800;color:var(--on-surface);">Dampak Lingkungan Anda</span>
                    </div>
                    <span class="badge badge-primary">
                        {{ number_format($totalWeight, 1, ',', '.') }} kg disetor
                    </span>
                </div>
                <p style="font-size:12.5px;color:var(--on-surface-variant);margin-bottom:var(--s-8);line-height:1.45;">
                    Target daur ulang Anda semester ini adalah <strong style="color:var(--primary);">{{ $targetWeight }} kg</strong>.
                    Terus setor sampah untuk mencapai target!
                </p>
                <div class="eco-progress-bar">
                    <div class="eco-progress-fill" style="width:{{ min($progressPercent, 100) }}%;"></div>
                </div>
                <div class="eco-progress-stats">
                    <span>{{ $progressPercent }}% Tercapai</span>
                    <span>Target: {{ $targetWeight }} kg</span>
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div>
                <div class="section-row" style="margin-top:0;">
                    <span class="section-title">Setoran &amp; Penarikan Terbaru</span>
                    <a href="{{ route('siswa.history') }}" class="section-link">
                        Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="transaction-list">
                    @forelse($recentTransactions as $tx)
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
                                        {{ $tx->type === 'setor' ? 'Setor ' . ($tx->wasteCategory->name ?? 'Sampah') : 'Tarik Tunai' }}
                                    </span>
                                    <span class="transaction-date">{{ $tx->created_at->format('d M Y, H:i') }}</span>
                                    @if($tx->type === 'setor')
                                        <span class="transaction-weight-tag">
                                            <i class="bi bi-recycle" style="font-size:10px;color:var(--primary);"></i>
                                            {{ number_format($tx->weight, 1, ',', '.') }} kg
                                        </span>
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
                                <p class="empty-desc">Belum ada transaksi. Setor sampah ke Bank Sampah sekolah untuk memulai!</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
