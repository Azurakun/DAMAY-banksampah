@extends('layouts.app')
@section('title', 'Daftar Pengguna Sistem — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Header --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="border-color:var(--accent);">
            <i class="bi bi-people" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Manajemen Pengguna</div>
            <div class="greeting-meta">
                <i class="bi bi-shield-lock" style="margin-right:3px;"></i>Total terdaftar: <strong style="color:var(--primary);">{{ $users->count() }} Akun</strong>
            </div>
        </div>
        <div style="margin-left:auto;">
            <button type="button" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent);" onclick="toggleAddStaffForm()">
                <i class="bi bi-person-plus"></i> + Registrasi Staf Baru
            </button>
        </div>
    </div>

    {{-- COLLAPSIBLE ADD STAFF CARD --}}
    <div id="add-staff-card" class="card animate-fade-in" style="display:none;margin-bottom:var(--s-20);border-left:4px solid var(--accent);">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h3 style="font-size:14.5px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:6px;">
                <i class="bi bi-person-plus" style="color:var(--accent);"></i>
                Registrasi Staf Baru (Operator / Wali Kelas)
            </h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleAddStaffForm()" style="padding:4px 8px;font-size:11px;">Tutup</button>
        </div>

        <form action="{{ route('manajer.staff.register.post') }}" method="POST">
            @csrf

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:var(--s-12);margin-bottom:var(--s-12);">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="staff-name" class="form-label" style="font-size:11.5px;">Nama Lengkap</label>
                    <input type="text" id="staff-name" name="name" class="form-control" placeholder="Nama lengkap staf" required style="font-size:13px;padding:8px 10px;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="staff-email" class="form-label" style="font-size:11.5px;">Alamat Email</label>
                    <input type="email" id="staff-email" name="email" class="form-control" placeholder="email@ecobank.com" required style="font-size:13px;padding:8px 10px;">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));gap:var(--s-12);margin-bottom:var(--s-16);">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="staff-password" class="form-label" style="font-size:11.5px;">Password Sementara</label>
                    <input type="password" id="staff-password" name="password" class="form-control" placeholder="Minimal 6 karakter" required style="font-size:13px;padding:8px 10px;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="staff-role" class="form-label" style="font-size:11.5px;">Peran (Role)</label>
                    <select id="staff-role" name="role" class="form-control" required style="font-size:13px;padding:8px 10px;" onchange="toggleStaffClassField(this.value)">
                        <option value="" disabled selected>-- Pilih Peran --</option>
                        <option value="operator">Operator</option>
                        <option value="walikelas">Wali Kelas</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="staff-class-group" style="display:none;margin-bottom:var(--s-16);">
                <label class="form-label" style="font-size:11.5px;">Kelas Asuhan Wali Kelas (Bisa memilih lebih dari satu)</label>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(110px, 1fr));gap:var(--s-8);background:var(--surface-container);padding:var(--s-12);border-radius:var(--r-sm);border:1.5px solid var(--outline-variant);max-height:120px;overflow-y:auto;">
                    @foreach($classrooms as $classroom)
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="checkbox" name="classroom_ids[]" id="cls-{{ $classroom->id }}" value="{{ $classroom->id }}" style="width:16px;height:16px;accent-color:var(--accent);cursor:pointer;">
                            <label for="cls-{{ $classroom->id }}" style="font-size:12.5px;font-weight:600;color:var(--on-surface);cursor:pointer;margin:0;">{{ $classroom->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAddStaffForm()">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent);">Daftarkan Staf</button>
            </div>
        </form>
    </div>

    {{-- ===== FILTER PANEL ===== --}}
    <form method="GET" action="{{ route('manajer.users') }}" id="filter-form">
        <div class="card" style="padding:var(--s-16);margin-bottom:var(--s-20);">
            <div style="font-size:12.5px;font-weight:700;color:var(--on-surface-variant);text-transform:uppercase;letter-spacing:.05em;margin-bottom:var(--s-12);">
                <i class="bi bi-funnel" style="margin-right:4px;color:var(--accent);"></i> Saring & Cari Pengguna
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:var(--s-12);">
                {{-- Search Box --}}
                <div style="grid-column: span 2;">
                    <label style="font-size:11px;font-weight:600;color:var(--on-surface-variant);display:block;margin-bottom:4px;">Kata Kunci Pencarian</label>
                    <input type="text" name="search" class="form-control" style="font-size:13px;padding:8px 10px;" placeholder="Cari nama, email, NISN, kelas..." value="{{ $searchQuery }}">
                </div>

                {{-- Filter: Peran --}}
                <div>
                    <label style="font-size:11px;font-weight:600;color:var(--on-surface-variant);display:block;margin-bottom:4px;">Peran (Role)</label>
                    <select name="role" class="form-control" style="font-size:13px;padding:8px 10px;" onchange="this.form.submit()">
                        <option value="">-- Semua Peran --</option>
                        <option value="manajer" {{ $roleFilter === 'manajer' ? 'selected' : '' }}>Manajer</option>
                        <option value="walikelas" {{ $roleFilter === 'walikelas' ? 'selected' : '' }}>Wali Kelas</option>
                        <option value="operator" {{ $roleFilter === 'operator' ? 'selected' : '' }}>Operator</option>
                        <option value="siswa" {{ $roleFilter === 'siswa' ? 'selected' : '' }}>Siswa (Nasabah)</option>
                    </select>
                </div>

                {{-- Filter: Status --}}
                <div>
                    <label style="font-size:11px;font-weight:600;color:var(--on-surface-variant);display:block;margin-bottom:4px;">Status Akun</label>
                    <select name="status" class="form-control" style="font-size:13px;padding:8px 10px;" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Disetujui (Aktif)</option>
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:var(--s-16);display:flex;justify-content:flex-end;align-items:center;gap:var(--s-12);">
                @if($searchQuery || $roleFilter || $statusFilter)
                    <a href="{{ route('manajer.users') }}" style="font-size:12.5px;color:var(--primary);text-decoration:none;font-weight:600;">
                        <i class="bi bi-x-circle"></i> Bersihkan Filter
                    </a>
                @endif
                <button type="submit" class="btn btn-outline-primary btn-sm" style="height:36px;font-size:12.5px;">
                    <i class="bi bi-search"></i> Cari Data
                </button>
            </div>
        </div>
    </form>

    {{-- User Table --}}
    <div class="card">
        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Pengguna</th>
                        <th>Email / Telepon</th>
                        <th>Peran (Role)</th>
                        <th>Status</th>
                        <th>NISN / Kelas</th>
                        <th>Terdaftar</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div style="font-weight:700;color:var(--on-surface);">{{ $user->name }}</div>
                            </td>
                            <td>
                                <div style="font-size:13px;">{{ $user->email }}</div>
                                <div style="font-size:11.5px;color:var(--on-surface-variant);"><i class="bi bi-telephone"></i> {{ $user->phone ?? '—' }}</div>
                            </td>
                            <td>
                                @if($user->role === 'manajer')
                                    <span class="badge" style="background:#6366f1;color:white;">Manajer</span>
                                @elseif($user->role === 'walikelas')
                                    <span class="badge badge-accent">Wali Kelas</span>
                                @elseif($user->role === 'operator')
                                    <span class="badge badge-teal">Operator</span>
                                @else
                                    <span class="badge badge-primary">Siswa</span>
                                @endif
                            </td>
                            <td>
                                @if($user->status === 'approved')
                                    <span class="badge badge-teal"><i class="bi bi-check-circle" style="font-size:10px;margin-right:3px;"></i> Aktif</span>
                                @elseif($user->status === 'pending')
                                    <span class="badge badge-warning"><i class="bi bi-hourglass-split" style="font-size:10px;margin-right:3px;"></i> Pending</span>
                                @else
                                    <span class="badge badge-danger"><i class="bi bi-x-circle" style="font-size:10px;margin-right:3px;"></i> Ditolak</span>
                                @endif
                            </td>
                            <td>
                                @if($user->role === 'siswa')
                                    <div style="font-size:13px;">NISN: <code>{{ $user->nisn ?? '—' }}</code></div>
                                    <div style="font-size:12px;font-weight:600;color:var(--primary);">{{ $user->class ?? '—' }}</div>
                                @elseif($user->role === 'walikelas')
                                    <div style="font-size:12px;font-weight:700;color:var(--accent);">Asuhan: {{ $user->class ?? '—' }}</div>
                                @else
                                    <span style="color:var(--outline);font-size:12.5px;">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px;color:var(--on-surface-variant);">
                                {{ $user->created_at ? $user->created_at->format('d/m/y') : '—' }}
                            </td>
                            <td style="text-align:center; display:flex; justify-content:center; gap:6px;">
                                <button type="button" class="btn btn-outline-secondary btn-sm" style="padding:4px 8px;font-size:11px;min-height:auto; border-color:var(--primary); color:var(--primary);" onclick="showEditUserModal({{ json_encode($user) }}, {{ json_encode($user->classrooms->pluck('id')) }})">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                @if($user->id !== auth()->id())
                                    <button type="button" class="btn btn-outline-danger btn-sm" style="padding:4px 8px;font-size:11px;min-height:auto;" onclick="showDeleteConfirmModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ ucfirst($user->role) }}')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                @else
                                    <span class="badge" style="background:var(--surface-container);color:var(--outline); align-self:center;">Anda</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:var(--s-32);color:var(--on-surface-variant);">
                                <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                                    <i class="bi bi-person-x" style="font-size:36px;color:var(--outline);"></i>
                                    <span style="font-weight:700;">Pengguna Tidak Ditemukan</span>
                                    <span style="font-size:12.5px;">Silakan bersihkan filter atau gunakan kata kunci lain.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- HIDDEN DELETE FORM --}}
<form id="delete-user-form" method="POST" action="" style="display:none;">
    @csrf
    @method('DELETE')
</form>

{{-- MODAL KONFIRMASI HAPUS --}}
<div id="confirmModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);" class="animate-fade-in">
    <div class="card" style="width:90%;max-width:400px;padding:var(--s-24);border-radius:var(--r-lg);box-shadow:0 8px 32px rgba(0,0,0,0.15);">
        <div style="text-align:center;margin-bottom:var(--s-16);">
            <div id="modal-icon-container" style="width:56px;height:56px;border-radius:50%;margin:0 auto var(--s-16) auto;display:flex;align-items:center;justify-content:center;font-size:24px;background:rgba(220, 38, 38, 0.1);">
                <i id="modal-icon" class="bi bi-trash" style="color:#dc2626;"></i>
            </div>
            <h3 id="modal-title" style="font-size:16px;font-weight:800;color:var(--on-surface);margin-bottom:8px;">Hapus Akun Pengguna</h3>
            <p id="modal-body" style="font-size:13.5px;color:var(--on-surface-variant);line-height:1.5;"></p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s-12);">
            <button type="button" class="btn btn-outline-secondary w-full" onclick="closeConfirmModal()">Batal</button>
            <button type="button" id="confirm-submit-btn" class="btn btn-danger w-full" onclick="submitDeleteForm()">Ya, Hapus</button>
        </div>
    </div>
</div>

{{-- MODAL EDIT USER --}}
<div id="editUserModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);" class="animate-fade-in">
    <div class="card" style="width:95%;max-width:550px;padding:var(--s-32);border-radius:var(--r-lg);box-shadow:0 12px 40px rgba(0,0,0,0.2); border-top: 5px solid var(--primary); max-height: 90vh; overflow-y: auto;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--s-20);">
            <div>
                <h3 style="font-family:var(--font-display); font-size:20px; font-weight:800; color:var(--primary); margin-bottom:4px;">Edit Akun Pengguna</h3>
                <p style="font-size:13px; color:var(--on-surface-variant);">Peran: <strong id="edit-user-role-badge">—</strong></p>
            </div>
            <button type="button" onclick="closeEditUserModal()" style="background:none; border:none; font-size:28px; cursor:pointer; color:var(--on-surface-variant); line-height:1;">&times;</button>
        </div>

        <form id="edit-user-form" action="" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="edit-name" class="form-label">Nama Lengkap</label>
                <input type="text" id="edit-name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-email" class="form-label">Alamat Email</label>
                <input type="email" id="edit-email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-phone" class="form-label">Nomor Telepon</label>
                <input type="text" id="edit-phone" name="phone" class="form-control">
            </div>

            <div class="form-group">
                <label for="edit-status" class="form-label">Status Akun</label>
                <select id="edit-status" name="status" class="form-control" style="padding-top:0; padding-bottom:0;" required>
                    <option value="approved">Aktif (Approved)</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="rejected">Ditolak (Rejected)</option>
                </select>
            </div>

            {{-- Classroom Selection for Siswa --}}
            <div class="form-group" id="edit-classroom-siswa-group" style="display:none;">
                <label for="edit-classroom-id" class="form-label">Kelas Asal</label>
                <select id="edit-classroom-id" name="classroom_id" class="form-control" style="padding-top:0; padding-bottom:0;">
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Classroom Checkboxes for Wali Kelas --}}
            <div class="form-group" id="edit-classroom-walikelas-group" style="display:none;">
                <label class="form-label">Kelas Asuhan (Wali Kelas bisa mengampu lebih dari 1 kelas)</label>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:var(--s-8); background:var(--surface-dim); padding:var(--s-12); border-radius:var(--r-md); border:1px solid var(--outline-variant); max-height:150px; overflow-y:auto;">
                    @foreach($classrooms as $cls)
                        <label style="display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; cursor:pointer;">
                            <input type="checkbox" name="classroom_ids[]" value="{{ $cls->id }}" class="edit-classroom-checkbox">
                            {{ $cls->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="border-top: 1px dashed var(--outline-variant); margin: var(--s-24) 0; padding-top: var(--s-16);">
                <h4 style="font-size:13.5px; font-weight:800; color:var(--on-surface); margin-bottom:var(--s-12); display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-key" style="color:var(--accent);"></i> Ganti Password (Opsional)
                </h4>
                <div class="form-group">
                    <label for="edit-password" class="form-label">Password Baru</label>
                    <input type="password" id="edit-password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--s-12); margin-top:var(--s-28);">
                <button type="button" class="btn btn-outline-secondary w-full" onclick="closeEditUserModal()">Batal</button>
                <button type="submit" class="btn btn-primary w-full">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Toggle registration form
    function toggleAddStaffForm() {
        const card = document.getElementById('add-staff-card');
        if (card.style.display === 'none') {
            card.style.display = 'block';
            document.getElementById('staff-name').focus();
        } else {
            card.style.display = 'none';
        }
    }

    // Toggle class field based on role selection
    function toggleStaffClassField(role) {
        const classGroup = document.getElementById('staff-class-group');
        
        if (role === 'walikelas') {
            classGroup.style.display = 'block';
        } else {
            classGroup.style.display = 'none';
            const checkboxes = classGroup.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
        }
    }

    // Modal elements
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-body');
    const deleteForm = document.getElementById('delete-user-form');

    function showDeleteConfirmModal(id, name, role) {
        deleteForm.action = `/manajer/users/${id}`;
        
        modalTitle.textContent = 'Hapus Akun Pengguna';
        modalBody.innerHTML = `Apakah Anda yakin ingin <strong>menghapus permanen</strong> akun <strong>${name}</strong> (${role})?<br><span style="color:var(--danger);font-weight:700;font-size:11.5px;display:block;margin-top:8px;"><i class="bi bi-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan!</span>`;
        
        modal.style.display = 'flex';
    }

    function closeConfirmModal() {
        modal.style.display = 'none';
    }

    function submitDeleteForm() {
        deleteForm.submit();
    }

    function showEditUserModal(user, assignedClassrooms) {
        // Populate inputs
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-phone').value = user.phone || '';
        document.getElementById('edit-status').value = user.status;
        document.getElementById('edit-password').value = '';
        
        // Form Action
        document.getElementById('edit-user-form').action = `/manajer/users/${user.id}`;
        
        // Role badge
        const rolesMap = {
            'manajer': 'Manajer',
            'walikelas': 'Wali Kelas',
            'operator': 'Operator',
            'siswa': 'Siswa (Nasabah)'
        };
        document.getElementById('edit-user-role-badge').innerText = rolesMap[user.role] || user.role;

        // Hide/Show classroom elements based on role
        const siswaGroup = document.getElementById('edit-classroom-siswa-group');
        const waliGroup = document.getElementById('edit-classroom-walikelas-group');
        
        siswaGroup.style.display = 'none';
        waliGroup.style.display = 'none';

        // Uncheck all classroom checkboxes
        const checkboxes = document.querySelectorAll('.edit-classroom-checkbox');
        checkboxes.forEach(cb => cb.checked = false);

        if (user.role === 'siswa') {
            siswaGroup.style.display = 'block';
            document.getElementById('edit-classroom-id').value = user.classroom_id || '';
        } else if (user.role === 'walikelas') {
            waliGroup.style.display = 'block';
            // Check boxes for assigned classrooms
            if (assignedClassrooms && assignedClassrooms.length > 0) {
                checkboxes.forEach(cb => {
                    if (assignedClassrooms.includes(parseInt(cb.value))) {
                        cb.checked = true;
                    }
                });
            }
        }

        document.getElementById('editUserModal').style.display = 'flex';
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').style.display = 'none';
    }
</script>
@endsection
