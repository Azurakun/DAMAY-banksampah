@extends('layouts.app')
@section('title', 'Kelola Kelas — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Header --}}
    <div class="greeting-row" style="margin-bottom: var(--s-32);">
        <div class="greeting-avatar" style="border-color:var(--accent);">
            <i class="bi bi-building" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Manajemen Kelas & Tahun Pelajaran</div>
            <div class="greeting-meta">
                Tahun Pelajaran Aktif: <strong style="color:var(--primary); font-size:14px;"><i class="bi bi-calendar3"></i> {{ $currentSchoolYear }}</strong>
            </div>
        </div>
        <div style="margin-left:auto; display:flex; gap:10px;">
            <button type="button" class="btn btn-outline-secondary btn-sm" style="border-color:var(--primary); color:var(--primary);" onclick="showEditYearModal()">
                <i class="bi bi-pencil-square"></i> Ubah Manual
            </button>
            <button type="button" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent);box-shadow:var(--shadow-stamp);" onclick="showRolloverModal()">
                <i class="bi bi-arrow-up-right-circle"></i> Tahun Ajaran Baru
            </button>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr; gap:var(--s-24);" class="grid-2-lg">
        
        {{-- LEFT COLUMN: ADD CLASSROOM FORM --}}
        <div>
            <div class="card" style="border-left: 4px solid var(--primary); margin-bottom: var(--s-20);">
                <h3 style="font-size:15px;font-weight:800;color:var(--primary);margin-bottom:var(--s-16);display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-plus-circle"></i> Tambah Kelas Baru
                </h3>
                
                <form action="{{ route('manajer.classrooms.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Kelas</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control" 
                            placeholder="Contoh: XII RPL 1 atau 10 RPL 2" 
                            required 
                            value="{{ old('name') }}"
                        >
                        @error('name')
                            <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full btn-sm" style="height: 42px;">
                        <i class="bi bi-check-lg"></i> Simpan Kelas
                    </button>
                </form>
            </div>

            <div class="card" style="background: var(--accent-container); border: 1px dashed var(--accent); color: var(--on-accent-container);">
                <h4 style="font-size: 13.5px; font-weight: 800; margin-bottom: var(--s-8); display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-info-circle-fill" style="color:var(--accent);"></i> Informasi Rollover Kelas
                </h4>
                <p style="font-size: 12.5px; line-height: 1.5; margin: 0;">
                    Ketika Anda mengklik tombol <strong>Tahun Ajaran Baru</strong>:
                </p>
                <ul style="font-size: 12.5px; line-height: 1.5; margin: var(--s-8) 0 0 0; padding-left: var(--s-20);">
                    <li>Sistem otomatis menaikkan tahun ajaran (contoh: 2025/2026 -> 2026/2027).</li>
                    <li>Siswa kelas 10 naik ke kelas 11 (misal: 10 RPL 1 -> 11 RPL 1).</li>
                    <li>Siswa kelas 11 naik ke kelas 12 (misal: 11 RPL 1 -> 12 RPL 1).</li>
                    <li>Siswa kelas 12 diluluskan (status kelas berubah menjadi <strong>Lulus</strong>).</li>
                </ul>
            </div>
        </div>

        {{-- RIGHT COLUMN: CLASSROOMS LIST --}}
        <div>
            <div class="card" style="padding:0; overflow:hidden;">
                <div style="padding:var(--s-16) var(--s-20); background:var(--surface-container); border-bottom:1px solid var(--outline-variant); display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="font-size:14px;font-weight:800;color:var(--on-surface);margin:0;">
                        <i class="bi bi-list-stars" style="color:var(--primary);"></i> Daftar Kelas Aktif
                    </h3>
                    <span class="badge badge-primary">{{ $classrooms->count() }} Kelas</span>
                </div>
                
                <div class="table-overflow">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="padding-left:var(--s-20);">Nama Kelas</th>
                                <th style="text-align:center;">Jumlah Nasabah (Siswa)</th>
                                <th style="text-align:center; padding-right:var(--s-20);">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classrooms as $classroom)
                                <tr>
                                    <td style="padding-left:var(--s-20); font-weight:700; color:var(--primary);">
                                        {{ $classroom->name }}
                                    </td>
                                    <td style="text-align:center; font-weight:600;">
                                        @if($classroom->students_count > 0)
                                            <span style="color: var(--success);">{{ $classroom->students_count }} Siswa</span>
                                        @else
                                            <span style="color: var(--on-surface-variant); font-weight:normal; opacity:0.6;">0 Siswa</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center; padding-right:var(--s-20);">
                                        @if($classroom->students_count == 0)
                                            <form action="{{ route('manajer.classrooms.destroy', $classroom->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-xs" style="padding: 2px 6px;">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-ghost btn-xs" style="opacity: 0.5; cursor: not-allowed; padding: 2px 6px;" title="Kelas tidak dapat dihapus karena memiliki siswa aktif">
                                                <i class="bi bi-trash"></i> Terkunci
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:var(--s-40);color:var(--on-surface-variant);">
                                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                            <i class="bi bi-building-x" style="font-size:36px;color:var(--outline);"></i>
                                            <span style="font-weight:700;">Belum Ada Kelas</span>
                                            <span style="font-size:12.5px;">Gunakan form di sebelah kiri untuk membuat kelas pertama Anda.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- MODAL KONFIRMASI ROLLOVER TAHUN AJARAN BARU --}}
<div id="rolloverModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);" class="animate-fade-in">
    <div class="card" style="width:95%;max-width:500px;padding:var(--s-28);border-radius:var(--r-lg);box-shadow:0 12px 40px rgba(0,0,0,0.2); border-top: 5px solid var(--accent);">
        <div style="text-align:center;margin-bottom:var(--s-20);">
            <div style="width:64px;height:64px;border-radius:50%;margin:0 auto var(--s-16) auto;display:flex;align-items:center;justify-content:center;font-size:30px;background:rgba(184, 121, 43, 0.1);">
                <i class="bi bi-arrow-up-right-circle" style="color:var(--accent);"></i>
            </div>
            <h3 style="font-size:18px;font-weight:800;color:var(--on-surface);margin-bottom:8px;">Konfirmasi Tahun Pelajaran Baru</h3>
            <p style="font-size:13.5px;color:var(--on-surface-variant);line-height:1.5;">
                Anda akan menaikkan tingkat seluruh kelas dan memulai tahun pelajaran baru.
            </p>
        </div>

        <div style="background:var(--surface-container); border: 1px solid var(--outline-variant); padding:var(--s-16); border-radius:var(--r-md); margin-bottom:var(--s-24); font-size:13px; text-align:left;">
            <div style="display:flex; justify-content:space-between; margin-bottom: 6px;">
                <span>Tahun Pelajaran Saat Ini:</span>
                <strong style="color: var(--primary);">{{ $currentSchoolYear }}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; font-weight:700; border-top: 1px dashed var(--outline); padding-top:6px;">
                <span>Tahun Pelajaran Baru:</span>
                @php
                    if (preg_match('/^(\d{4})\/(\d{4})$/', $currentSchoolYear, $matches)) {
                        $nextYearStart = intval($matches[1]) + 1;
                        $nextYearEnd = intval($matches[2]) + 1;
                        $newYear = $nextYearStart . '/' . $nextYearEnd;
                    } else {
                        $newYear = '2026/2027';
                    }
                @endphp
                <strong style="color: var(--accent);">{{ $newYear }}</strong>
            </div>
            
            <div style="color: var(--danger); font-weight:700; font-size:12px; margin-top:12px; display:flex; gap:6px;">
                <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;"></i>
                <span>Tindakan ini akan merubah kelas siswa secara permanen. Pastikan Anda telah melakukan backup database jika diperlukan!</span>
            </div>
        </div>

        <form action="{{ route('manajer.schoolyear.rollover') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s-12);">
                <button type="button" class="btn btn-outline-secondary w-full" onclick="closeRolloverModal()">Batal</button>
                <button type="submit" class="btn btn-primary w-full" style="background:var(--accent); border-color:var(--accent);">Mulai Tahun Baru</button>
            </div>
        </form>
    </div>
</div>

@endsection

{{-- MODAL UBAH TAHUN AJARAN SECARA MANUAL --}}
@php
    $currentYearNum = intval(date('Y'));
    $years = [];
    for ($i = -2; $i <= 10; $i++) {
        $startYear = $currentYearNum + $i;
        $endYear = $startYear + 1;
        $years[] = "$startYear/$endYear";
    }
    if (!in_array($currentSchoolYear, $years)) {
        array_unshift($years, $currentSchoolYear);
    }
@endphp
<div id="editYearModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);" class="animate-fade-in">
    <div class="card" style="width:95%;max-width:450px;padding:var(--s-32);border-radius:var(--r-lg);box-shadow:0 12px 40px rgba(0,0,0,0.2); border-top: 5px solid var(--primary);">
        <div style="text-align:center;margin-bottom:var(--s-20);">
            <div style="width:64px;height:64px;border-radius:50%;margin:0 auto var(--s-16) auto;display:flex;align-items:center;justify-content:center;font-size:30px;background:var(--primary-container);">
                <i class="bi bi-calendar3" style="color:var(--primary);"></i>
            </div>
            <h3 style="font-size:18px;font-weight:800;color:var(--on-surface);margin-bottom:8px;">Ubah Tahun Pelajaran</h3>
            <p style="font-size:13.5px;color:var(--on-surface-variant);line-height:1.5;">
                Ubah label Tahun Pelajaran global tanpa menaikkan kelas siswa.
            </p>
        </div>

        <form action="{{ route('manajer.schoolyear.update') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:var(--s-24);">
                <label for="school_year" class="form-label" style="display:block; margin-bottom:8px;">Tahun Pelajaran</label>
                <select id="school_year" name="school_year" class="form-control" required style="padding-top:0; padding-bottom:0;">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ $currentSchoolYear === $yr ? 'selected' : '' }}>
                            {{ $yr }}
                        </option>
                    @endforeach
                </select>
                <span class="form-hint" style="margin-top:10px; display:block; line-height:1.4; color:var(--on-surface-variant);"><i class="bi bi-info-circle"></i> Ini hanya mengganti label teks Tahun Pelajaran aktif dan tidak mengubah kelas murid.</span>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s-12);margin-top:var(--s-24);">
                <button type="button" class="btn btn-outline-secondary w-full" onclick="closeEditYearModal()">Batal</button>
                <button type="submit" class="btn btn-primary w-full">Simpan</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function showRolloverModal() {
        document.getElementById('rolloverModal').style.display = 'flex';
    }

    function closeRolloverModal() {
        document.getElementById('rolloverModal').style.display = 'none';
    }

    function showEditYearModal() {
        document.getElementById('editYearModal').style.display = 'flex';
    }

    function closeEditYearModal() {
        document.getElementById('editYearModal').style.display = 'none';
    }
</script>
@endsection
