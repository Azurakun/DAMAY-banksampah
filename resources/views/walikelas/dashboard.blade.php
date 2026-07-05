@extends('layouts.app')
@section('title', 'Portal Wali Kelas — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    {{-- Header --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="border-color:var(--teal);">
            <i class="bi bi-mortarboard" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Portal Wali Kelas</div>
            <div class="greeting-meta">
                <i class="bi bi-person-badge" style="margin-right:3px;"></i>{{ $teacher->name }}
                &nbsp;·&nbsp;
                <strong style="color:var(--primary);">{{ $className }}</strong>
            </div>
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

    {{-- ===== FILTER PANEL ===== --}}
    <form method="GET" action="{{ route('walikelas.dashboard') }}" id="filter-form">
        <div class="card" style="padding:var(--s-16);margin-bottom:var(--s-20);">
            <div style="font-size:12.5px;font-weight:700;color:var(--on-surface-variant);text-transform:uppercase;letter-spacing:.05em;margin-bottom:var(--s-12);">
                <i class="bi bi-funnel" style="margin-right:4px;color:var(--primary);"></i> Filter Data Kontribusi
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s-12);">
                {{-- Filter: Kelas --}}
                <div>
                    <label style="font-size:11px;font-weight:600;color:var(--on-surface-variant);display:block;margin-bottom:4px;">Kelas</label>
                    <select id="filter-kelas" name="kelas" class="form-control" style="font-size:13px;padding:8px 10px;"
                            onchange="document.getElementById('filter-angkatan').value=''; this.form.submit();">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($allClasses as $kls)
                            <option value="{{ $kls }}" {{ $selectedClass === $kls && !$selectedAngkatan ? 'selected' : '' }}>
                                {{ $kls }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter: Angkatan --}}
                <div>
                    <label style="font-size:11px;font-weight:600;color:var(--on-surface-variant);display:block;margin-bottom:4px;">Angkatan</label>
                    <select id="filter-angkatan" name="angkatan" class="form-control" style="font-size:13px;padding:8px 10px;"
                            onchange="document.getElementById('filter-kelas').value=''; this.form.submit();">
                        <option value="">-- Semua Angkatan --</option>
                        @foreach($allAngkatan as $ang)
                            <option value="{{ $ang }}" {{ $selectedAngkatan === $ang ? 'selected' : '' }}>
                                {{ $ang }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Active filter badge --}}
            @if($selectedAngkatan || $selectedClass)
                <div style="margin-top:var(--s-10);display:flex;align-items:center;gap:var(--s-8);">
                    <span style="font-size:11.5px;color:var(--on-surface-variant);">Menampilkan:</span>
                    <span class="badge badge-teal" style="font-size:11.5px;">{{ $className }}</span>
                    <a href="{{ route('walikelas.dashboard') }}" style="font-size:11px;color:var(--primary);text-decoration:none;font-weight:600;">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            @endif
        </div>
    </form>

    {{-- Class Stats --}}
    <div class="operator-stats-grid">
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-teal"><i class="bi bi-recycle"></i></div>
            <div class="stat-item-label">Total Sampah Kelas</div>
            <div class="stat-item-value">{{ number_format($classTotalWeight, 1, ',', '.') }} <span style="font-size:14px;">kg</span></div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-green"><i class="bi bi-wallet2"></i></div>
            <div class="stat-item-label">Total Tabungan</div>
            <div class="stat-item-value" style="font-size:15px;">Rp {{ number_format($classTotalBalance, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-amber"><i class="bi bi-people"></i></div>
            <div class="stat-item-label">Jumlah Siswa</div>
            <div class="stat-item-value">{{ $students->count() }}</div>
        </div>
        <div class="stat-item-box">
            <div class="stat-item-icon stat-icon-blue"><i class="bi bi-calculator"></i></div>
            <div class="stat-item-label">Rata-rata Saldo</div>
            <div class="stat-item-value" style="font-size:15px;">
                Rp {{ number_format($students->count() > 0 ? ($classTotalBalance / $students->count()) : 0, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Student Leaderboard for Class --}}
    <div class="card">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-bar-chart-line" style="color:var(--primary);"></i>
                Peringkat Kontribusi — {{ $className }}
            </h2>
            <span class="badge badge-teal">{{ $students->count() }} Siswa</span>
        </div>

        <div class="leaderboard-list">
            @forelse($students as $index => $student)
                @php $rank = $index + 1; @endphp
                <div class="leaderboard-item {{ $rank === 1 ? 'rank-1-item' : '' }}" style="cursor:pointer;" onclick="viewStudentDetails({{ $student->id }})">
                    <div class="leaderboard-student">
                        <span class="rank-badge {{ $rank <= 3 ? 'rank-'.$rank : '' }}">{{ $rank }}</span>
                        <div>
                            <div class="student-name">{{ $student->name }}</div>
                            <div class="student-class">
                                NISN: {{ $student->nisn }}
                                &nbsp;·&nbsp;
                                Saldo: Rp {{ number_format($student->balance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:var(--s-16); flex-shrink:0;">
                        <div class="leaderboard-points" style="margin-right: 0;">
                            <span>
                                <i class="bi bi-star" style="font-size:12px;color:var(--accent);margin-right:3px;"></i>
                                {{ number_format($student->points, 0, ',', '.') }}
                            </span>
                            <span style="display:block;font-size:9.5px;font-weight:600;color:var(--on-surface-variant);text-transform:uppercase;">Poin</span>
                        </div>
                        <div style="font-size: 16px; color: var(--primary); opacity: 0.7;">
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-person-x empty-icon"></i>
                    <h3 class="empty-title">Belum Ada Siswa</h3>
                    <p class="empty-desc">Belum ada siswa terdaftar di kelas asuhan Anda.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

{{-- MODAL DETAIL SISWA --}}
<div id="studentDetailModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);" class="animate-fade-in">
    <div class="card" style="width:95%;max-width:650px;padding:var(--s-32);border-radius:var(--r-lg);box-shadow:0 12px 40px rgba(0,0,0,0.2); border-top: 5px solid var(--primary); max-height: 90vh; overflow-y: auto;">
        
        {{-- Modal Header --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--s-20);">
            <div>
                <h3 id="detail-student-name" style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--primary); margin-bottom:4px;">Nama Siswa</h3>
                <p style="font-size:13px; color:var(--on-surface-variant);">
                    NISN: <span id="detail-student-nisn" style="font-family:var(--font-mono); font-weight:700;">—</span> &nbsp;·&nbsp;
                    Kelas: <span id="detail-student-class" style="font-weight:700;">—</span>
                </p>
            </div>
            <button type="button" onclick="closeStudentDetailModal()" style="background:none; border:none; font-size:28px; cursor:pointer; color:var(--on-surface-variant); line-height:1;">&times;</button>
        </div>

        {{-- Contact Info Cards --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--s-12); margin-bottom:var(--s-20); background:var(--surface-dim); padding:var(--s-16); border-radius:var(--r-md); border: 1px solid var(--outline-variant);">
            <div style="font-size:12.5px;">
                <div style="color:var(--on-surface-variant); font-weight:600; margin-bottom:2px;"><i class="bi bi-envelope"></i> Email</div>
                <strong id="detail-student-email" style="word-break:break-all;">—</strong>
            </div>
            <div style="font-size:12.5px;">
                <div style="color:var(--on-surface-variant); font-weight:600; margin-bottom:2px;"><i class="bi bi-telephone"></i> Telepon</div>
                <strong id="detail-student-phone">—</strong>
            </div>
        </div>

        {{-- Grid Info --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--s-16); margin-bottom:var(--s-24);">
            <div class="card" style="margin-bottom:0; padding: var(--s-16); background:var(--primary-container); border-color:var(--primary);">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--primary); margin-bottom:4px;">Saldo Tabungan</div>
                <div style="font-size:20px; font-weight:800; color:var(--primary); font-family:var(--font-mono);">Rp <span id="detail-student-balance">0</span></div>
            </div>
            <div class="card" style="margin-bottom:0; padding: var(--s-16); background:var(--accent-container); border-color:var(--accent);">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--on-accent-container); margin-bottom:4px;">Akumulasi Poin</div>
                <div style="font-size:20px; font-weight:800; color:var(--accent); font-family:var(--font-mono);"><span id="detail-student-points">0</span> <span style="font-size:12px; color:var(--on-surface-variant);">Poin</span></div>
            </div>
        </div>

        {{-- Waste Category Breakdown --}}
        <div style="margin-bottom:var(--s-24);">
            <h4 style="font-size:13px; font-weight:800; color:var(--on-surface); margin-bottom:12px; text-transform:uppercase; letter-spacing:0.5px;"><i class="bi bi-pie-chart" style="color:var(--primary);"></i> Rincian Tabungan Per Kategori Sampah</h4>
            <div id="detail-categories-list" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(130px, 1fr)); gap:var(--s-8);">
                <!-- Category Chips generated dynamically -->
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div>
            <h4 style="font-size:13px; font-weight:800; color:var(--on-surface); margin-bottom:12px; text-transform:uppercase; letter-spacing:0.5px;"><i class="bi bi-clock-history" style="color:var(--primary);"></i> 10 Transaksi Terakhir</h4>
            <div class="table-overflow" style="border: 1px solid var(--outline-variant); border-radius: var(--r-md); overflow: hidden;">
                <table class="data-table" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th style="padding:10px 12px !important;">Tanggal</th>
                            <th style="padding:10px 12px !important;">Tipe</th>
                            <th style="padding:10px 12px !important;">Kategori</th>
                            <th style="padding:10px 12px !important;">Berat</th>
                            <th style="padding:10px 12px !important;">Jumlah</th>
                            <th style="padding:10px 12px !important;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="detail-transactions-tbody">
                        <!-- Table rows generated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="margin-top:var(--s-28); text-align:right;">
            <button type="button" class="btn btn-primary" onclick="closeStudentDetailModal()" style="min-width:100px;">Tutup</button>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
    function viewStudentDetails(studentId) {
        // Show loading state or clear fields
        document.getElementById('detail-student-name').innerText = 'Memuat...';
        document.getElementById('detail-student-nisn').innerText = '—';
        document.getElementById('detail-student-class').innerText = '—';
        document.getElementById('detail-student-email').innerText = '—';
        document.getElementById('detail-student-phone').innerText = '—';
        document.getElementById('detail-student-balance').innerText = '0';
        document.getElementById('detail-student-points').innerText = '0';
        document.getElementById('detail-categories-list').innerHTML = '';
        document.getElementById('detail-transactions-tbody').innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">Memuat data...</td></tr>';
        
        document.getElementById('studentDetailModal').style.display = 'flex';

        const detailUrl = "{{ route('walikelas.student.detail', ['id' => ':id']) }}".replace(':id', studentId);
        fetch(detailUrl)
            .then(res => res.json())
            .then(data => {
                // Populate bio
                document.getElementById('detail-student-name').innerText = data.student.name;
                document.getElementById('detail-student-nisn').innerText = data.student.nisn;
                document.getElementById('detail-student-class').innerText = data.student.class;
                document.getElementById('detail-student-email').innerText = data.student.email;
                document.getElementById('detail-student-phone').innerText = data.student.phone;
                document.getElementById('detail-student-balance').innerText = data.student.balance;
                document.getElementById('detail-student-points').innerText = data.student.points;

                // Populate categories breakdown
                const categoriesList = document.getElementById('detail-categories-list');
                categoriesList.innerHTML = '';
                
                let hasWaste = false;
                data.categories.forEach(cat => {
                    const totalW = parseFloat(cat.total_weight);
                    const totalVal = parseInt(cat.total_amount);
                    if (totalW > 0) hasWaste = true;
                    
                    const card = document.createElement('div');
                    card.className = 'card';
                    card.style.padding = '12px';
                    card.style.marginBottom = '0';
                    card.style.textAlign = 'center';
                    card.style.borderLeft = '3px solid var(--primary)';
                    
                    card.innerHTML = `
                        <div style="font-size:11px; font-weight:700; color:var(--on-surface-variant); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${cat.name}</div>
                        <div style="font-size:14px; font-weight:800; color:var(--primary); margin:4px 0;">${totalW.toFixed(1)} ${cat.unit}</div>
                        <div style="font-size:10px; color:var(--accent); font-weight:600;">Rp ${totalVal.toLocaleString('id-ID')}</div>
                    `;
                    categoriesList.appendChild(card);
                });
                
                if (!hasWaste) {
                    categoriesList.innerHTML = '<div style="grid-column:1/-1; text-align:center; font-size:13px; color:var(--on-surface-variant); padding: 10px;">Belum ada kontribusi setoran sampah.</div>';
                }

                // Populate transactions
                const tbody = document.getElementById('detail-transactions-tbody');
                tbody.innerHTML = '';
                if (data.transactions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color:var(--on-surface-variant);">Belum ada riwayat transaksi.</td></tr>';
                } else {
                    data.transactions.forEach(t => {
                        const tr = document.createElement('tr');
                        
                        let statusBadge = '';
                        if (t.status === 'Berhasil') {
                            statusBadge = '<span class="badge badge-success" style="background:var(--success-container); color:var(--success); font-size:10px; padding:2px 8px; border-radius:var(--r-full);">Berhasil</span>';
                        } else if (t.status === 'Menunggu') {
                            statusBadge = '<span class="badge badge-warning" style="background:var(--warning-container); color:var(--accent); font-size:10px; padding:2px 8px; border-radius:var(--r-full);">Menunggu</span>';
                        } else {
                            statusBadge = '<span class="badge badge-danger" style="background:var(--danger-container); color:var(--danger); font-size:10px; padding:2px 8px; border-radius:var(--r-full);">Batal</span>';
                        }

                        const typeText = t.type === 'Setor' 
                            ? '<span style="color:var(--primary); font-weight:700;"><i class="bi bi-arrow-down-left-circle"></i> Setor</span>'
                            : '<span style="color:var(--accent); font-weight:700;"><i class="bi bi-arrow-up-right-circle"></i> Tarik</span>';

                        tr.innerHTML = `
                            <td style="padding:10px 12px !important;">${t.date}</td>
                            <td style="padding:10px 12px !important;">${typeText}</td>
                            <td style="padding:10px 12px !important;">${t.category}</td>
                            <td style="padding:10px 12px !important; font-family:var(--font-mono);">${t.weight ? t.weight + ' kg' : '—'}</td>
                            <td style="padding:10px 12px !important; font-family:var(--font-mono); font-weight:700; color:var(--on-surface);">Rp ${t.amount}</td>
                            <td style="padding:10px 12px !important;">${statusBadge}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('detail-student-name').innerText = 'Error';
                document.getElementById('detail-transactions-tbody').innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color:var(--danger);">Gagal memuat data.</td></tr>';
            });
    }

    function closeStudentDetailModal() {
        document.getElementById('studentDetailModal').style.display = 'none';
    }
</script>
@endsection
