@extends('layouts.app')
@section('title', 'Daftar Akun Nasabah — EcoBank SMKN 2 Indramayu')

@section('content')
<!-- Premium Animated Background -->
<div class="auth-bg-animation">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<div class="auth-layout-container animate-fade-in" style="margin-bottom:var(--s-32);">
    <div class="auth-layout">
        
        <!-- Left Column: Marketing / Branding Info -->
        <div class="auth-info-col">
            <div class="auth-badge">
                <i class="bi bi-recycle"></i> EcoBank SMKN 2 Indramayu
            </div>
            <h1 class="auth-hero-title">Ayo Menabung Sampah!</h1>
            <p class="auth-hero-desc">
                Daftarkan diri Anda sebagai nasabah EcoBank sekarang juga. Mulailah memilah sampah dari rumah/kelas, kumpulkan saldo tabungan, dapatkan poin gamifikasi, dan berkontribusi langsung menjaga kebersihan lingkungan sekolah.
            </p>
            
            <div class="auth-features">
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Proses Pendaftaran Cepat & Gratis</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Persetujuan Akun Otomatis oleh Wali Kelas</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Pantau Saldo & Peringkat (Leaderboard) Siswa</span>
                </div>
            </div>

            <div class="auth-footer-note">
                Sudah memiliki akun? <a href="{{ route('login') }}">Masuk di sini</a>
            </div>
        </div>

        <!-- Right Column: Registration Card -->
        <div class="auth-form-col">
            <div class="login-card" style="width: 100%; max-width: 480px;">
                
                <!-- Card Header -->
                <div class="auth-card-header">
                    <h2 class="auth-card-title">Daftar Akun</h2>
                    <p class="auth-card-subtitle">Bergabung sebagai Nasabah Bank Sampah</p>
                </div>

                <!-- Form Body -->
                <div class="login-form-body" style="padding-top: var(--s-12);">
                    <form action="{{ route('register.post') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="bi bi-person" style="margin-right:4px;"></i>Nama Lengkap
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                placeholder="Nama lengkap Anda"
                                value="{{ old('name') }}"
                                required
                                autofocus
                            >
                            @error('name')
                                <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--s-12);">
                            <div class="form-group">
                                <label for="nisn" class="form-label">
                                    <i class="bi bi-card-text" style="margin-right:4px;"></i>NISN
                                </label>
                                <input
                                    type="text"
                                    id="nisn"
                                    name="nisn"
                                    class="form-control"
                                    placeholder="Contoh: 12345678"
                                    value="{{ old('nisn') }}"
                                    required
                                >
                                @error('nisn')
                                    <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="classroom_id" class="form-label">
                                    <i class="bi bi-building" style="margin-right:4px;"></i>Kelas
                                </label>
                                <select id="classroom_id" name="classroom_id" class="form-control" required style="padding-top:0; padding-bottom:0;">
                                    <option value="" disabled selected>Pilih Kelas</option>
                                    @foreach($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                            {{ $classroom->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('classroom_id')
                                    <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone" style="margin-right:4px;"></i>Nomor Telepon / WA (Opsional)
                            </label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                class="form-control"
                                placeholder="Contoh: 08123456789"
                                value="{{ old('phone') }}"
                            >
                            @error('phone')
                                <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope" style="margin-right:4px;"></i>Alamat Email
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="email@ecobank.com"
                                value="{{ old('email') }}"
                                required
                            >
                            @error('email')
                                <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock" style="margin-right:4px;"></i>Password
                            </label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Minimal 6 karakter"
                                required
                            >
                            @error('password')
                                <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">
                                <i class="bi bi-lock-fill" style="margin-right:4px;"></i>Konfirmasi Password
                            </label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Ulangi password"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-full" style="margin-top:var(--s-8);">
                            <i class="bi bi-person-plus"></i> Daftar Akun
                        </button>
                    </form>

                    <div style="text-align:center;margin-top:var(--s-16);">
                        <span style="font-size:13px;color:var(--on-surface-variant);">Sudah punya akun? </span>
                        <a href="{{ route('login') }}" style="font-size:13px;font-weight:700;color:var(--primary);text-decoration:none;">Masuk di sini</a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
