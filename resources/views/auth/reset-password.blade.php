@extends('layouts.app')
@section('title', 'Reset Password — EcoBank SMKN 2 Indramayu')

@section('content')
<!-- Premium Animated Background -->
<div class="auth-bg-animation">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<div class="auth-layout-container animate-fade-in">
    <div class="auth-layout">
        
        <!-- Left Column: Marketing / Branding Info -->
        <div class="auth-info-col">
            <div class="auth-badge">
                <i class="bi bi-recycle"></i> EcoBank SMKN 2 Indramayu
            </div>
            <h1 class="auth-hero-title">Atur Password Baru</h1>
            <p class="auth-hero-desc">
                Silakan buat password baru yang kuat, aman, dan mudah Anda ingat untuk melindungi keamanan akun tabungan digital Anda.
            </p>
            
            <div class="auth-features">
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Password Minimal 6 Karakter</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Gunakan Kombinasi Huruf & Angka</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Perlindungan Kredensial Terenkripsi</span>
                </div>
            </div>

            <div class="auth-footer-note">
                Memerlukan bantuan lebih lanjut? <a href="https://wa.me/#" target="_blank">Hubungi Admin</a>
            </div>
        </div>

        <!-- Right Column: Reset Password Form Card -->
        <div class="auth-form-col">
            <div class="login-card" style="width: 100%; max-width: 420px;">
                
                <!-- Card Header -->
                <div class="auth-card-header">
                    <h2 class="auth-card-title">Password Baru</h2>
                    <p class="auth-card-subtitle">Buat dan konfirmasi password baru untuk akun Anda</p>
                </div>

                <!-- Form Body -->
                <div class="login-form-body" style="padding-top: var(--s-12);">
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope" style="margin-right:4px;"></i>Alamat Email
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="Masukkan alamat email"
                                value="{{ old('email', $email) }}"
                                required
                                readonly
                                style="background-color: var(--surface-container); color: var(--on-surface-variant); cursor: not-allowed;"
                            >
                            @error('email')
                                <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock" style="margin-right:4px;"></i>Password Baru
                            </label>
                            <div style="position:relative;">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Masukkan password baru"
                                    required
                                    style="padding-right: 48px;"
                                >
                                <button
                                    type="button"
                                    id="toggle-pw"
                                    onclick="togglePw('password', 'pw-eye')"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--on-surface-variant);font-size:16px;padding:4px;"
                                    tabindex="-1"
                                    aria-label="Tampilkan password"
                                >
                                    <i class="bi bi-eye" id="pw-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">
                                <i class="bi bi-lock-check" style="margin-right:4px;"></i>Konfirmasi Password Baru
                            </label>
                            <div style="position:relative;">
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    placeholder="Konfirmasi password baru"
                                    required
                                    style="padding-right: 48px;"
                                >
                                <button
                                    type="button"
                                    id="toggle-pw-conf"
                                    onclick="togglePw('password_confirmation', 'pw-eye-conf')"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--on-surface-variant);font-size:16px;padding:4px;"
                                    tabindex="-1"
                                    aria-label="Tampilkan password"
                                >
                                    <i class="bi bi-eye" id="pw-eye-conf"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full" style="margin-top: var(--s-8);">
                            <i class="bi bi-check-lg"></i> Perbarui Password
                        </button>
                    </form>

                    <div style="text-align:center;margin-top:var(--s-20);border-top: 1px dashed var(--outline-variant);padding-top:var(--s-16);">
                        <a href="{{ route('login') }}" style="font-size:13px;font-weight:700;color:var(--primary);text-decoration:none;">
                            Batal dan Kembali
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function togglePw(inputId, eyeId) {
        const pw = document.getElementById(inputId);
        const eye = document.getElementById(eyeId);
        if (pw.type === 'password') {
            pw.type = 'text';
            eye.className = 'bi bi-eye-slash';
        } else {
            pw.type = 'password';
            eye.className = 'bi bi-eye';
        }
    }
</script>
@endsection
