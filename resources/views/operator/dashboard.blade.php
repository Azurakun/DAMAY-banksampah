@extends('layouts.app')
@section('title', 'Dasbor Operator — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    {{-- Greeting --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="text-decoration:none;border-color:var(--teal);">
            <i class="bi bi-person-workspace" style="color:white;font-size:24px;"></i>
        </div>
        <div>
            <div class="greeting-name">Dasbor Operator</div>
            <div class="greeting-meta"><i class="bi bi-person-badge" style="margin-right:3px;"></i>{{ $operator->name }}</div>
        </div>
        <div style="margin-left:auto;">
            <div class="badge badge-primary" style="font-size:11px;">
                <i class="bi bi-calendar3"></i> {{ today()->isoFormat('D MMMM Y') }}
            </div>
        </div>
    </div>

    {{-- KPI Stats --}}
    <div class="operator-stats-grid">
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-green"><i class="bi bi-recycle"></i></div>
            <div class="stat-item-label">Setoran Hari Ini</div>
            <div class="stat-item-value">{{ $todayDepositsCount }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-teal"><i class="bi bi-bag-check"></i></div>
            <div class="stat-item-label">Berat Hari Ini</div>
            <div class="stat-item-value">{{ number_format($todayWeight, 1, ',', '.') }} <span style="font-size:14px;">kg</span></div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-amber"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-item-label">Antrean Tarik</div>
            <div class="stat-item-value">{{ $pendingTarik->count() }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-blue"><i class="bi bi-people"></i></div>
            <div class="stat-item-label">Total Siswa</div>
            <div class="stat-item-value">{{ $totalStudents ?? '—' }}</div>
        </div>
    </div>

    {{-- Dashboard Grid --}}
    <div class="dashboard-grid">
        
        {{-- Left Column: Quick Actions & Search Input --}}
        <div class="dashboard-column-left">
            {{-- Quick Actions --}}
            <div class="action-grid">
                <a href="{{ route('operator.students.register') }}" class="action-card">
                    <div class="action-icon"><i class="bi bi-person-plus"></i></div>
                    <span class="action-label">Registrasi Siswa</span>
                </a>
                <a href="{{ route('operator.history') }}" class="action-card">
                    <div class="action-icon"><i class="bi bi-clock-history"></i></div>
                    <span class="action-label">Riwayat Log</span>
                </a>
            </div>

            {{-- Student Search → Setor --}}
            <div class="card" style="border-color:var(--primary); margin-bottom:0;">
                <h2 style="font-size:16px;font-weight:800;color:var(--primary);margin-bottom:var(--s-6);display:flex;align-items:center;gap:var(--s-8);">
                    <i class="bi bi-search"></i> Input Timbangan Sampah Baru
                </h2>
                <p style="font-size:12.5px;color:var(--on-surface-variant);margin-bottom:var(--s-12);">
                    Ketik nama atau NISN siswa untuk memulai pencatatan setoran sampah.
                </p>
                <div class="autocomplete-wrapper">
                    <input
                        type="text"
                        id="studentSearch"
                        class="form-control"
                        style="border-width:2px;font-weight:600;"
                        placeholder="Cari nama atau NISN siswa..."
                        autocomplete="off"
                    >
                    <div id="suggestionList" class="autocomplete-list" style="display:none;"></div>
                </div>
            </div>
        </div>

        {{-- Right Column: Pending Withdrawals --}}
        <div class="dashboard-column-right">
            <div class="card" style="margin-bottom:0;">
                <div class="flex-between" style="margin-bottom:var(--s-16);">
                    <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-6); margin:0;">
                        <i class="bi bi-wallet2" style="color:var(--primary);"></i> Antrean Tarik Dana
                    </h2>
                    @if($pendingTarik->count() > 0)
                        <span class="badge badge-warning">{{ $pendingTarik->count() }} Menunggu</span>
                    @else
                        <span class="badge badge-success">Kosong</span>
                    @endif
                </div>

                <div class="transaction-list">
                    @forelse($pendingTarik as $tx)
                        <div style="background:var(--surface);border:1px solid var(--outline-variant);border-radius:var(--r-md);padding:var(--s-16);box-shadow:var(--shadow-xs);margin-bottom:var(--s-8);">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:var(--s-12);margin-bottom:var(--s-12);">
                                <div style="display:flex;gap:var(--s-12);">
                                    <div class="transaction-icon-badge tarik">
                                        <i class="bi bi-cash-stack" style="font-size:18px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:14px;font-weight:800;color:var(--on-surface);">{{ $tx->student->name }}</div>
                                        <div style="font-size:11.5px;color:var(--on-surface-variant);margin-top:2px;">
                                            Kelas: {{ $tx->student->class }} · NISN: {{ $tx->student->nisn }}
                                        </div>
                                        <div style="font-size:11px;color:var(--on-surface-variant);margin-top:2px;">
                                            Saldo: Rp {{ number_format($tx->student->balance, 0, ',', '.') }}
                                        </div>
                                        @if($tx->note)
                                            <div style="font-size:11px;color:var(--on-surface-variant);font-style:italic;margin-top:2px;">"{{ $tx->note }}"</div>
                                        @endif
                                    </div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div style="font-size:17px;font-weight:800;color:var(--accent);">Rp {{ number_format($tx->amount, 0, ',', '.') }}</div>
                                    <div style="font-size:10.5px;color:var(--on-surface-variant);margin-top:2px;">{{ $tx->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--s-8);border-top:1px solid var(--outline-variant);padding-top:var(--s-12);">
                                <form action="{{ route('operator.withdraw.cancel', $tx->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-full btn-sm">
                                        <i class="bi bi-x-circle"></i> Tolak
                                    </button>
                                </form>
                                <form action="{{ route('operator.withdraw.approve', $tx->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-full btn-sm">
                                        <i class="bi bi-check-circle"></i> Setujui & Bayar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-check-circle empty-icon" style="color:var(--success);"></i>
                            <p class="empty-desc" style="margin:0;">Tidak ada antrean penarikan dana saat ini.</p>
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
    const searchInput = document.getElementById('studentSearch');
    const listContainer = document.getElementById('suggestionList');

    searchInput.addEventListener('input', () => {
        const val = searchInput.value.trim();
        if (val.length < 2) { listContainer.style.display = 'none'; return; }

        fetch(`/operator/search?query=${encodeURIComponent(val)}`)
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) {
                    listContainer.innerHTML = '<div style="padding:var(--s-16);font-size:13px;color:var(--on-surface-variant);text-align:center;">Siswa tidak ditemukan</div>';
                } else {
                    listContainer.innerHTML = data.map(s => `
                        <div class="autocomplete-item" onclick="selectStudent(${s.id})">
                            <div>
                                <div class="autocomplete-student-name">${s.name}</div>
                                <div class="autocomplete-student-nisn">NISN: ${s.nisn} &nbsp;·&nbsp; Kelas: ${s.class}</div>
                            </div>
                            <i class="bi bi-arrow-right" style="color:var(--primary);"></i>
                        </div>
                    `).join('');
                }
                listContainer.style.display = 'block';
            })
            .catch(() => listContainer.style.display = 'none');
    });

    document.addEventListener('click', e => {
        if (e.target !== searchInput) listContainer.style.display = 'none';
    });

    function selectStudent(id) {
        window.location.href = `/operator/setor/${id}`;
    }
</script>
@endsection
