@extends('layouts.app')
@section('title', 'Profil Manajer — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="max-width:520px;margin:0 auto;margin-bottom:var(--s-32);">

    <a href="{{ route('manajer.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Dasbor Portal
    </a>

    <div class="card" style="padding:0;overflow:hidden;">

        {{-- Profile Hero --}}
        <div class="profile-hero" style="background:linear-gradient(180deg, var(--accent-container) 0%, var(--surface) 100%);">
            <div class="profile-avatar-circle" style="margin:0 auto var(--s-12);background:var(--accent);">
                <i class="bi bi-shield-check" style="color:white;font-size:46px;"></i>
            </div>
            <div class="profile-name">{{ $manager->name }}</div>
            <div class="profile-role">
                <span class="badge badge-accent" style="background:var(--accent);color:white;">Manajer Sekolah</span>
            </div>
        </div>

        {{-- Form Body --}}
        <div style="padding:var(--s-20);">

            {{-- Info Read-only Grid --}}
            <div class="info-grid" style="margin-bottom:var(--s-20);">
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-envelope" style="margin-right:4px;"></i>Email</span>
                    <span class="info-value">{{ $manager->email }}</span>
                </div>
            </div>

            {{-- Editable Fields --}}
            <form action="{{ route('manajer.profile.update') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $manager->name) }}" required>
                    @error('name')
                        <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Nomor Telepon / WhatsApp</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $manager->phone) }}" placeholder="Contoh: 081234567890">
                    @error('phone')
                        <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                    @enderror
                </div>

                <div class="divider-dashed" style="margin:var(--s-20) 0;"></div>
                
                <h3 style="font-size:13.5px;font-weight:800;color:var(--on-surface);margin-bottom:var(--s-12);">
                    <i class="bi bi-key" style="margin-right:4px;color:var(--accent);"></i> Ganti Password (Opsional)
                </h3>

                <div class="form-group">
                    <label for="password" class="form-label">Password Baru</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                    @error('password')
                        <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>

                <button type="submit" class="btn btn-primary w-full" style="background:var(--accent);border-color:var(--accent);">
                    <i class="bi bi-check-lg"></i> Simpan Perubahan
                </button>

            </form>

            <div class="divider-dashed" style="margin-top:var(--s-20);"></div>

            <form action="{{ route('logout') }}" method="POST" style="margin-top:var(--s-12);">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-full">
                    <i class="bi bi-box-arrow-right"></i> Keluar dari Aplikasi
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
