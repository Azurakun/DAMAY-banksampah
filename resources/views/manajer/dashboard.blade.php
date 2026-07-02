@extends('layouts.app')
@section('title', 'Portal Manajer — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    {{-- Header --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="border-color:var(--accent);">
            <i class="bi bi-shield-check" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Portal Manajemen</div>
            <div class="greeting-meta"><i class="bi bi-person-badge" style="margin-right:3px;"></i>{{ $manager->name }}</div>
        </div>
        <div style="margin-left:auto;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </div>

    {{-- KPI Stats --}}
    <div class="operator-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-green"><i class="bi bi-people"></i></div>
            <div class="stat-item-label">Total Siswa</div>
            <div class="stat-item-value">{{ $totalStudents }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-teal"><i class="bi bi-recycle"></i></div>
            <div class="stat-item-label">Total Sampah</div>
            <div class="stat-item-value">{{ number_format($totalWeightRecycled, 1, ',', '.') }} <span style="font-size:14px;">kg</span></div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-amber"><i class="bi bi-wallet2"></i></div>
            <div class="stat-item-label">Uang Beredar</div>
            <div class="stat-item-value" style="font-size:15px;">Rp {{ number_format($totalSchoolBalance, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-green" style="background:rgba(255, 193, 7, 0.1);color:#ffc107;"><i class="bi bi-star"></i></div>
            <div class="stat-item-label">Total Poin</div>
            <div class="stat-item-value" style="font-size:15px;color:#d97706;">{{ number_format($totalSchoolPoints, 0, ',', '.') }} <span style="font-size:11px;font-weight:700;color:var(--on-surface-variant);">Poin</span></div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-blue"><i class="bi bi-receipt"></i></div>
            <div class="stat-item-label">Total Transaksi</div>
            <div class="stat-item-value">{{ $totalTransactionsCount }}</div>
        </div>
    </div>

    {{-- Class Performance Table --}}
    <div class="card">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-buildings" style="color:var(--primary);"></i> Performa Kelas
            </h2>
            <span class="badge badge-primary">{{ $classPerformance->count() }} Kelas</span>
        </div>

        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th style="text-align:center;">Siswa</th>
                        <th style="text-align:right;">Total Tabungan</th>
                        <th style="text-align:right;">Total Poin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classPerformance as $class)
                        <tr>
                            <td style="font-weight:700;color:var(--primary);">{{ $class->class }}</td>
                            <td style="text-align:center;">
                                <span class="badge badge-teal">{{ $class->student_count }}</span>
                            </td>
                            <td style="text-align:right;font-weight:700;">Rp {{ number_format($class->total_balance, 0, ',', '.') }}</td>
                            <td style="text-align:right;">
                                <span style="font-weight:800;color:var(--accent);display:inline-flex;align-items:center;gap:3px;">
                                    <i class="bi bi-star" style="font-size:11px;color:var(--accent);"></i>
                                    {{ number_format($class->total_points, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:var(--s-24);color:var(--on-surface-variant);">
                                Belum ada data kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="display:grid;grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--s-20); margin-top: var(--s-20);">
        {{-- Recent Transactions --}}
        <div class="card" style="margin-top:0;">
            <div class="flex-between" style="margin-bottom:var(--s-16);">
                <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                    <i class="bi bi-receipt" style="color:var(--primary);"></i> Log Transaksi Terbaru
                </h2>
                <span class="badge badge-primary">10 Terakhir</span>
            </div>

            <div class="transaction-list">
                @forelse($allRecentTransactions as $tx)
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
                                    {{ $tx->type === 'setor' ? 'Setoran ' . ($tx->wasteCategory->name ?? 'Sampah') : 'Penarikan Dana' }}
                                </span>
                                <span class="transaction-date">
                                    <strong>{{ $tx->student->name }}</strong> ({{ $tx->student->class }})
                                </span>
                                <span class="transaction-date">
                                    {{ $tx->created_at->format('d/m/Y H:i') }} · {{ $tx->operator->name ?? 'Sistem' }}
                                </span>
                                @if($tx->type === 'setor')
                                    <span class="transaction-weight-tag">
                                        <i class="bi bi-recycle" style="font-size:10px;color:var(--primary);"></i>
                                        {{ number_format($tx->weight, 2, ',', '.') }} kg
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
                    <div class="empty-state">
                        <i class="bi bi-folder empty-icon"></i>
                        <p class="empty-desc">Belum ada log transaksi.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Top 5 Nasabah Teraktif (Gamification Leaderboard) --}}
        <div class="card" style="margin-top:0;">
            <div class="flex-between" style="margin-bottom:var(--s-16);">
                <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                    <i class="bi bi-trophy" style="color:var(--accent);"></i> Top 5 Siswa Teraktif
                </h2>
                <span class="badge badge-accent">Nasabah Teraktif</span>
            </div>

            <div class="leaderboard-list">
                @forelse($topStudents as $index => $student)
                    @php $rank = $index + 1; @endphp
                    <div class="leaderboard-item {{ $rank === 1 ? 'rank-1-item' : '' }}" style="padding:12px;margin-bottom:8px;border-radius:var(--r-md);">
                        <div class="leaderboard-student">
                            <span class="rank-badge {{ $rank <= 3 ? 'rank-'.$rank : '' }}">{{ $rank }}</span>
                            <div>
                                <div class="student-name" style="font-weight:700;font-size:13.5px;">{{ $student->name }}</div>
                                <div class="student-class" style="font-size:11.5px;color:var(--on-surface-variant);">
                                    Kelas: {{ $student->class }} &nbsp;·&nbsp; Saldo: Rp {{ number_format($student->balance, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        <div class="leaderboard-points" style="text-align:right;">
                            <span style="font-weight:800;color:var(--accent);display:inline-flex;align-items:center;gap:3px;font-size:13.5px;">
                                <i class="bi bi-star" style="font-size:12px;color:var(--accent);"></i>
                                {{ number_format($student->points, 0, ',', '.') }}
                            </span>
                            <span style="display:block;font-size:9px;font-weight:600;color:var(--on-surface-variant);text-transform:uppercase;">Poin</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-star empty-icon"></i>
                        <p class="empty-desc">Belum ada data siswa terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
