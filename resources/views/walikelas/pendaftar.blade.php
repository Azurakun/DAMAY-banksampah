@extends('layouts.app')
@section('title', 'Persetujuan Pendaftar Baru — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    {{-- Header --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="border-color:var(--teal);">
            <i class="bi bi-person-check" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Pendaftar Baru</div>
            <div class="greeting-meta">
                <i class="bi bi-mortarboard" style="margin-right:3px;"></i>Kelas Bimbingan: <strong style="color:var(--primary);">{{ $className }}</strong>
            </div>
        </div>
        <div style="margin-left:auto;">
            <a href="{{ route('walikelas.dashboard') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-speedometer2"></i> Dasbor Kelas
            </a>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="card" style="padding:var(--s-16);margin-bottom:var(--s-20);">
        <p style="font-size:13.5px;color:var(--on-surface-variant);margin-bottom:0;line-height:1.5;">
            Di bawah ini adalah daftar calon nasabah (siswa) yang mendaftar mandiri untuk kelas <strong>{{ $className }}</strong>. 
            Anda harus memvalidasi data diri mereka terlebih dahulu sebelum menyetujui akses login mereka ke sistem EcoBank.
        </p>
    </div>

    {{-- Main Form for Bulk Action --}}
    <form id="bulk-form" method="POST" action="">
        @csrf

        <div class="card">
            <div class="flex-between" style="margin-bottom:var(--s-16);">
                <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                    <i class="bi bi-people" style="color:var(--primary);"></i>
                    Daftar Calon Pendaftar
                </h2>
                <span class="badge badge-teal" id="selected-count">0 Terpilih</span>
            </div>

            {{-- Bulk Actions Bar --}}
            <div class="flex-between" style="background:var(--surface-container);padding:12px;border-radius:var(--r-md);margin-bottom:var(--s-16);gap:12px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" id="select-all" style="width:18px;height:18px;accent-color:var(--primary);cursor:pointer;">
                    <label for="select-all" style="font-size:13px;font-weight:700;color:var(--on-surface);cursor:pointer;">Pilih Semua</label>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="showConfirmModal('reject')">
                        <i class="bi bi-x-circle"></i> Tolak Terpilih
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="showConfirmModal('approve')">
                        <i class="bi bi-check-circle"></i> Setujui Terpilih
                    </button>
                </div>
            </div>

            <div class="table-overflow">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center;"></th>
                            <th>Nama Siswa</th>
                            <th>NISN</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Waktu Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingStudents as $student)
                            <tr>
                                <td style="text-align:center;">
                                    <input type="checkbox" name="ids[]" value="{{ $student->id }}" class="student-checkbox" style="width:18px;height:18px;accent-color:var(--primary);cursor:pointer;" onchange="updateSelectedCount()">
                                </td>
                                <td style="font-weight:700;color:var(--on-surface);">{{ $student->name }}</td>
                                <td><code style="font-size:12.5px;">{{ $student->nisn }}</code></td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->phone ?? '-' }}</td>
                                <td style="font-size:12px;color:var(--on-surface-variant);">{{ $student->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:var(--s-32);color:var(--on-surface-variant);">
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                        <i class="bi bi-clipboard2-check" style="font-size:36px;color:var(--outline);"></i>
                                        <span style="font-weight:700;">Tidak ada pendaftar baru</span>
                                        <span style="font-size:12.5px;">Seluruh pengajuan pendaftaran siswa kelas Anda telah diproses.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

{{-- MODAL KONFIRMASI --}}
<div id="confirmModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);" class="animate-fade-in">
    <div class="card" style="width:90%;max-width:400px;padding:var(--s-24);border-radius:var(--r-lg);box-shadow:0 8px 32px rgba(0,0,0,0.15);">
        <div style="text-align:center;margin-bottom:var(--s-16);">
            <div id="modal-icon-container" style="width:56px;height:56px;border-radius:50%;margin:0 auto var(--s-16) auto;display:flex;align-items:center;justify-content:center;font-size:24px;">
                <i id="modal-icon" class="bi"></i>
            </div>
            <h3 id="modal-title" style="font-size:16px;font-weight:800;color:var(--on-surface);margin-bottom:8px;">Konfirmasi Aksi</h3>
            <p id="modal-body" style="font-size:13.5px;color:var(--on-surface-variant);line-height:1.5;"></p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s-12);">
            <button type="button" class="btn btn-outline-secondary w-full" onclick="closeConfirmModal()">Batal</button>
            <button type="button" id="confirm-submit-btn" class="btn w-full" onclick="submitBulkForm()">Ya, Konfirmasi</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const form = document.getElementById('bulk-form');
    const selectAllCheckbox = document.getElementById('select-all');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    const selectedCountBadge = document.getElementById('selected-count');
    
    // Modal elements
    const modal = document.getElementById('confirmModal');
    const modalIconContainer = document.getElementById('modal-icon-container');
    const modalIcon = document.getElementById('modal-icon');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-body');
    const confirmBtn = document.getElementById('confirm-submit-btn');

    let currentActionType = '';

    // Handle select all
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            studentCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateSelectedCount();
        });
    }

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        selectedCountBadge.textContent = `${checkedCount} Terpilih`;
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkedCount === studentCheckboxes.length && studentCheckboxes.length > 0;
        }
    }

    function showConfirmModal(action) {
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        if (checkedCount === 0) {
            alert('Silakan pilih minimal satu siswa terlebih dahulu.');
            return;
        }

        currentActionType = action;

        // Customise modal based on action (approve / reject)
        if (action === 'approve') {
            form.action = "{{ route('walikelas.pendaftar.approve') }}";
            modalTitle.textContent = 'Setujui Pendaftar';
            modalBody.innerHTML = `Apakah Anda yakin ingin <strong>menyetujui</strong> <strong style="color:var(--primary);">${checkedCount} pendaftar</strong> menjadi nasabah EcoBank?`;
            
            modalIcon.className = 'bi bi-check-circle';
            modalIcon.style.color = '#059669';
            modalIconContainer.style.background = 'rgba(5, 150, 105, 0.1)';
            
            confirmBtn.className = 'btn btn-primary w-full';
            confirmBtn.textContent = 'Ya, Setujui';
        } else if (action === 'reject') {
            form.action = "{{ route('walikelas.pendaftar.reject') }}";
            modalTitle.textContent = 'Tolak Pendaftar';
            modalBody.innerHTML = `Apakah Anda yakin ingin <strong>menolak</strong> <strong style="color:var(--danger);">${checkedCount} pendaftar</strong>? Calon siswa ini tidak akan bisa login ke aplikasi.`;
            
            modalIcon.className = 'bi bi-x-circle';
            modalIcon.style.color = '#dc2626';
            modalIconContainer.style.background = 'rgba(220, 38, 38, 0.1)';
            
            confirmBtn.className = 'btn btn-danger w-full';
            confirmBtn.textContent = 'Ya, Tolak';
        }

        modal.style.display = 'flex';
    }

    function closeConfirmModal() {
        modal.style.display = 'none';
    }

    function submitBulkForm() {
        form.submit();
    }
</script>
@endsection
