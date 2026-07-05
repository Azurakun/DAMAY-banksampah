@extends('layouts.app')
@section('title', 'Profil Wali Kelas — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Breadcrumb / Header --}}
    <div style="margin-bottom: var(--s-24);">
        <a href="{{ route('walikelas.dashboard') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:var(--primary); text-decoration:none; margin-bottom: 8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Dasbor
        </a>
        <h2 style="font-family:var(--font-display); font-size:26px; font-weight:800; color:var(--primary); margin-top:4px;">Pengaturan Profil</h2>
    </div>

    {{-- Split Layout --}}
    <div class="dashboard-grid">
        
        {{-- Left Column: Identity Card --}}
        <div class="dashboard-column-left">
            <div class="card" style="padding:0; overflow:hidden; border-top: 5px solid var(--primary);">
                <div class="profile-hero" style="background:linear-gradient(180deg, var(--primary-container) 0%, var(--surface) 100%); padding: var(--s-32) var(--s-24); text-align:center;">
                    <div class="profile-avatar-circle" style="width:90px; height:90px; border-radius:50%; background:var(--primary); color:white; font-size:42px; display:flex; align-items:center; justify-content:center; margin:0 auto var(--s-16) auto; box-shadow: var(--shadow-sm);">
                        {{ strtoupper(substr($teacher->name, 0, 1)) }}
                    </div>
                    <h3 style="font-family:var(--font-display); font-size:20px; font-weight:800; color:var(--on-surface);">{{ $teacher->name }}</h3>
                    <div style="margin-top:6px;">
                        <span class="badge badge-primary" style="background:var(--primary); color:white; padding: 4px 12px; border-radius:var(--r-full); font-size:11px; font-weight:700;">Wali Kelas</span>
                    </div>
                </div>

                <div style="padding: var(--s-24) var(--s-28); border-top: 1px dashed var(--outline-variant);">
                    <h4 style="font-size:13.5px; font-weight:800; color:var(--on-surface); margin-bottom:var(--s-16); text-transform:uppercase; letter-spacing:0.5px;">Informasi Akun</h4>
                    
                    <div class="info-grid" style="display:flex; flex-direction:column; gap:var(--s-12);">
                        <div style="display:flex; justify-content:space-between; font-size:13.5px; border-bottom:1px solid var(--outline-variant); padding-bottom:var(--s-8);">
                            <span style="color:var(--on-surface-variant); font-weight:600;"><i class="bi bi-envelope" style="margin-right:6px;"></i> Email</span>
                            <strong style="color:var(--on-surface);">{{ $teacher->email }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13.5px; border-bottom:1px solid var(--outline-variant); padding-bottom:var(--s-8);">
                            <span style="color:var(--on-surface-variant); font-weight:600;"><i class="bi bi-telephone" style="margin-right:6px;"></i> Telepon</span>
                            <strong style="color:var(--on-surface);">{{ $teacher->phone ?? '—' }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13.5px;">
                            <span style="color:var(--on-surface-variant); font-weight:600;"><i class="bi bi-building" style="margin-right:6px;"></i> Kelas Asuhan</span>
                            <strong style="color:var(--primary);">
                                @if($teacher->classrooms->count() > 0)
                                    {{ implode(', ', $teacher->classrooms->pluck('name')->toArray()) }}
                                @else
                                    {{ $teacher->class ?? 'Belum Ditentukan' }}
                                @endif
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Edit Form Card --}}
        <div class="dashboard-column-right">
            <div class="card" style="padding: var(--s-32) var(--s-28); border-top: 5px solid var(--accent);">
                <h3 style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--on-surface); margin-bottom:var(--s-24); display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-pencil-square" style="color:var(--accent);"></i> Ubah Detail Profil
                </h3>

                <form action="{{ route('walikelas.profile.update') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $teacher->name) }}" required>
                        @error('name')
                            <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:6px; display:block;"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $teacher->phone) }}" placeholder="Contoh: 081234567890">
                        @error('phone')
                            <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:6px; display:block;"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div style="border-top: 1px dashed var(--outline-variant); margin: var(--s-24) 0; padding-top: var(--s-16);">
                        <h4 style="font-size:14px; font-weight:800; color:var(--on-surface); margin-bottom:var(--s-16); display:flex; align-items:center; gap:6px;">
                            <i class="bi bi-key" style="color:var(--accent);"></i> Keamanan (Ganti Password)
                        </h4>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin diganti">
                            @error('password')
                                <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:6px; display:block;"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div style="margin-top: var(--s-32);">
                        <button type="submit" class="btn btn-primary w-full" style="background:var(--accent); border-color:var(--accent); color:white; box-shadow:var(--shadow-stamp);">
                            <i class="bi bi-check-lg"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
