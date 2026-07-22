@extends('layouts.app')
@section('title', 'Registrasi Siswa — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    <a href="{{ route('walikelas.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Portal Wali Kelas
    </a>

    <div class="page-header">
        <div class="page-header-icon">
            <i class="bi bi-person-plus"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-title">Registrasi Nasabah Baru</h1>
            <p class="page-subtitle">Daftarkan siswa secara manual atau import CSV</p>
        </div>
    </div>

    {{-- Bulk skip logs --}}
    @if(session('bulk_skip_logs') && count(session('bulk_skip_logs')) > 0)
        <div class="bulk-error-card">
            <div style="display:flex;align-items:center;gap:var(--s-8);font-size:14px;font-weight:800;">
                <i class="bi bi-exclamation-triangle"></i>
                {{ count(session('bulk_skip_logs')) }} baris data CSV dilewati:
            </div>
            <div class="bulk-error-log">
                @foreach(session('bulk_skip_logs') as $log)
                    <div>• {{ $log }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="tabs">
        <button class="tab-btn active" id="tab-manual-btn" onclick="switchTab('manual')">
            <i class="bi bi-person-add"></i> Satu Siswa
        </button>
        <button class="tab-btn" id="tab-csv-btn" onclick="switchTab('csv')">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import CSV
        </button>
    </div>

    {{-- Tab: Manual --}}
    <div class="tab-panel active" id="tab-manual">
        <div class="card" style="margin-bottom:0;">
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin-bottom:var(--s-20);display:flex;align-items:center;gap:var(--s-6);">
                <i class="bi bi-person-vcard"></i> Formulir Registrasi Manual
            </h3>

            <form action="{{ route('walikelas.students.register.single') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Budi Santoso" value="{{ old('name') }}" required>
                    @error('name')<span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
                </div>

                <div class="grid-2" style="gap:var(--s-8);">
                    <div class="form-group">
                        <label for="nisn" class="form-label">NISN</label>
                        <input type="text" id="nisn" name="nisn" class="form-control" placeholder="Contoh: 12345678" value="{{ old('nisn') }}" required>
                        @error('nisn')<span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="classroom_id" class="form-label">Kelas</label>
                        <select id="classroom_id" name="classroom_id" class="form-control" required style="padding-top:0; padding-bottom:0;">
                            <option value="" disabled selected>Pilih Kelas</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('classroom_id')<span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Contoh: budi@ecobank.com" value="{{ old('email') }}" required>
                    @error('email')<span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Nomor Telepon (Opsional)</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Contoh: 081234567890" value="{{ old('phone') }}">
                    @error('phone')<span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password Akun (Opsional)</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan untuk default: password">
                    <span class="form-hint">Siswa dapat login dengan email dan password ini.</span>
                    @error('password')<span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn-primary w-full" style="height:50px;">
                    <i class="bi bi-person-check"></i> Simpan Data Siswa
                </button>
            </form>
        </div>
    </div>

    {{-- Tab: CSV --}}
    <div class="tab-panel" id="tab-csv">
        <div class="card" style="margin-bottom:0;">
            <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin-bottom:var(--s-16);display:flex;align-items:center;gap:var(--s-6);">
                <i class="bi bi-file-earmark-spreadsheet"></i> Import Massal via CSV
            </h3>

            <div class="info-grid" style="margin-bottom:var(--s-16);">
                <div class="info-row">
                    <span class="info-label">Kolom Wajib</span>
                    <span class="info-value" style="font-size:12px;">Nama · Email · NISN · Kelas</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kolom Opsional</span>
                    <span class="info-value" style="font-size:12px;">Telepon</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Default Password</span>
                    <span class="info-value"><code style="font-weight:800;font-size:12px;background:var(--surface-container);padding:1px 6px;border-radius:4px;">password</code></span>
                </div>
            </div>

            <div style="margin-bottom:var(--s-20);">
                <a id="downloadTemplate" href="#" class="btn btn-ghost w-full" style="height:44px;font-size:13px;">
                    <i class="bi bi-download"></i> Unduh Template CSV
                </a>
            </div>

            <form action="{{ route('walikelas.students.register.bulk') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">Upload Berkas CSV</label>
                    <div
                        style="border:2px dashed var(--outline-variant);border-radius:var(--r-lg);padding:var(--s-32) var(--s-16);text-align:center;background:var(--surface-container);cursor:pointer;transition:all var(--dur-base) var(--ease);"
                        onclick="document.getElementById('csvFile').click()"
                        id="dropzone"
                    >
                        <i class="bi bi-cloud-arrow-up" style="font-size:40px;color:var(--outline);display:block;margin-bottom:var(--s-8);"></i>
                        <span id="fileNameLabel" style="font-size:13px;font-weight:700;color:var(--on-surface-variant);">Klik untuk pilih berkas CSV (.csv)...</span>
                        <input type="file" id="csvFile" name="file" accept=".csv" style="display:none;" onchange="updateFileName(this)" required>
                    </div>
                    @error('file')<span class="form-error" style="margin-top:var(--s-4);"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn-secondary w-full" style="height:50px;">
                    <i class="bi bi-upload"></i> Proses &amp; Import Data CSV
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById(`tab-${tab}-btn`).classList.add('active');
        document.getElementById(`tab-${tab}`).classList.add('active');
    }

    // CSV template download
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('downloadTemplate');
        if (btn) {
            const csv = "Nama,Email,NISN,Kelas,Telepon\nBudi Santoso,budi@ecobank.com,12345678,XII RPL 1,081234567890\nSiti Aminah,siti@ecobank.com,87654321,XII RPL 1,082134567891";
            const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
            btn.href = url;
            btn.download = 'template_siswa_ecobank.csv';
        }
    });

    function updateFileName(input) {
        const label = document.getElementById('fileNameLabel');
        const zone  = document.getElementById('dropzone');
        if (input.files && input.files[0]) {
            label.textContent = input.files[0].name;
            label.style.color = 'var(--primary)';
            zone.style.borderColor = 'var(--primary)';
            zone.style.background  = 'var(--primary-container)';
        } else {
            label.textContent = 'Klik untuk pilih berkas CSV (.csv)...';
            label.style.color = 'var(--on-surface-variant)';
            zone.style.borderColor = 'var(--outline-variant)';
            zone.style.background  = 'var(--surface-container)';
        }
    }
</script>
@endsection
