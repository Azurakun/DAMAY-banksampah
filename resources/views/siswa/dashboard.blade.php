@extends('layouts.app')
@section('title', 'Beranda Siswa — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    @if(isset($weeklyReport) && $weeklyReport !== null)
        @php
            $status = $weeklyReport['status'];
            $league = $weeklyReport['league'];
            $points = $weeklyReport['points'];
            $rank = $weeklyReport['rank'];

            $leagueNames = [
                'bronze' => 'Bronze League',
                'silver' => 'Silver League',
                'gold' => 'Gold League',
                'diamond' => 'Diamond League'
            ];
            $leagueIcons = [
                'bronze' => '🥉',
                'silver' => '🛡️',
                'gold' => '👑',
                'diamond' => '💎'
            ];
            $currentLeagueName = $leagueNames[$league] ?? 'Bronze League';
            $currentLeagueIcon = $leagueIcons[$league] ?? '🥉';
        @endphp

        <style>
            @keyframes slideDown {
                from { transform: translateY(-20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
        </style>

        <div class="card card-glass" style="border-left: 6px solid var(--primary); padding: var(--s-20); margin-bottom: var(--s-20); position: relative; overflow: hidden; animation: slideDown 0.5s ease-out;">
            <div style="display: flex; gap: var(--s-16); align-items: center;">
                <div style="font-size: 48px; line-height: 1; animation: bounce 1.5s infinite; flex-shrink: 0;">
                    @if($status === 'promoted') 🎉 @elseif($status === 'demoted') ⚠️ @else 👍 @endif
                </div>
                <div style="flex: 1; min-width: 0;">
                    <h3 style="font-family: var(--font-display); font-size: 18px; font-weight: 800; color: var(--on-surface); margin-bottom: 4px;">
                        @if($status === 'promoted')
                            Selamat! Anda Naik Kasta!
                        @elseif($status === 'demoted')
                            Yah, Kasta Liga Anda Turun
                        @else
                            Anda Bertahan di Liga!
                        @endif
                    </h3>
                    <p style="font-size: 13.5px; color: var(--on-surface-variant); margin: 0; line-height: 1.45;">
                        @if($status === 'promoted')
                            Kerja bagus! Di turnamen liga minggu lalu, Anda mengumpulkan <strong>{{ number_format($points, 0, ',', '.') }} Poin</strong> dan berhasil finish di peringkat <strong>#{{ $rank }}</strong>. Anda resmi naik ke <strong>{{ $currentLeagueIcon }} {{ $currentLeagueName }}</strong>!
                        @elseif($status === 'demoted')
                            Sayang sekali, dengan perolehan <strong>{{ number_format($points, 0, ',', '.') }} Poin</strong> dan peringkat <strong>#{{ $rank }}</strong> minggu lalu, Anda turun ke kasta <strong>{{ $currentLeagueIcon }} {{ $currentLeagueName }}</strong>. Ayo kumpulkan lebih banyak sampah minggu ini agar bisa promosi kembali!
                        @else
                            Dengan perolehan <strong>{{ number_format($points, 0, ',', '.') }} Poin</strong> dan peringkat <strong>#{{ $rank }}</strong> minggu lalu, Anda tetap bertahan di kasta <strong>{{ $currentLeagueIcon }} {{ $currentLeagueName }}</strong>. Terus tingkatkan setoran Anda untuk promosi!
                        @endif
                    </p>
                </div>
                <button onclick="this.closest('.card').remove()" style="background: none; border: none; font-size: 20px; color: var(--on-surface-variant); cursor: pointer; padding: 4px; align-self: flex-start; opacity: 0.7; transition: opacity var(--dur-fast);">&times;</button>
            </div>
        </div>
    @endif

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

            {{-- Duolingo League Widget --}}
            @php
                $myLeague = $user->league ?? 'bronze';
                if ($myLeague === 'diamond') {
                    $leagueName = 'Diamond League (Rank A+)';
                    $leagueIcon = '💎';
                    $leagueColor = '#1D7A8C';
                } elseif ($myLeague === 'gold') {
                    $leagueName = 'Gold League (Rank A)';
                    $leagueIcon = '👑';
                    $leagueColor = '#B8792B';
                } elseif ($myLeague === 'silver') {
                    $leagueName = 'Silver League (Rank B)';
                    $leagueIcon = '🛡️';
                    $leagueColor = '#7F8C8D';
                } else {
                    $leagueName = 'Bronze League (Rank C)';
                    $leagueIcon = '🥉';
                    $leagueColor = '#A77044';
                }
            @endphp
            <div class="card" style="padding:var(--s-16);margin-bottom:var(--s-20);border-left:5px solid {{ $leagueColor }};">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <div style="display:flex;align-items:center;gap:var(--s-12);min-width:0;flex:1;">
                        <span style="font-size:32px;filter:drop-shadow(0 4px 6px rgba(0,0,0,0.1));flex-shrink:0;">{{ $leagueIcon }}</span>
                        <div style="min-width:0;flex:1;">
                            <div style="font-size:10px;font-weight:800;color:var(--outline);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Liga Gamifikasi</div>
                            <div style="font-size:14px;font-weight:800;color:var(--on-surface);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $leagueName }}</div>
                            <div style="font-size:11px;color:var(--on-surface-variant);margin-top:2px;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                <span>Poin Minggu Ini: <strong>{{ number_format($user->weekly_points, 0, ',', '.') }}</strong></span>
                                <span style="opacity: 0.5;">·</span>
                                <span id="dashboard-countdown" data-endtime="{{ \Carbon\Carbon::now()->endOfWeek()->timestamp }}" style="font-weight:600;color:var(--accent);">--</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('siswa.leaderboard') }}" class="btn btn-outline-primary btn-sm" style="font-size:11.5px;padding:6px 12px;min-height:auto;border-color:{{ $leagueColor }};color:{{ $leagueColor }};flex-shrink:0;height:32px;display:inline-flex;align-items:center;justify-content:center;">
                        Klasemen <i class="bi bi-chevron-right" style="font-size:10px;margin-left:4px;"></i>
                    </a>
                </div>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const countdownEl = document.getElementById('dashboard-countdown');
        if (countdownEl) {
            const endTime = parseInt(countdownEl.getAttribute('data-endtime')) * 1000;
            function updateCountdown() {
                const now = new Date().getTime();
                const diff = endTime - now;
                if (diff <= 0) {
                    countdownEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Periode Berakhir';
                    return;
                }
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                let text = '<i class="bi bi-clock"></i> ';
                if (days > 0) {
                    text += days + 'h ' + hours + 'j lagi';
                } else if (hours > 0) {
                    text += hours + 'j ' + minutes + 'm lagi';
                } else {
                    text += minutes + 'm lagi';
                }
                countdownEl.innerHTML = text;
            }
            updateCountdown();
            setInterval(updateCountdown, 60000);
        }
    });
</script>
@endsection
