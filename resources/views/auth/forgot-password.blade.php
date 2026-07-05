@extends('layouts.app')
@section('title', 'Lupa Password — EcoBank SMKN 2 Indramayu')

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
            <h1 class="auth-hero-title">Lupa Password?</h1>
            <p class="auth-hero-desc">
                Jangan khawatir! Masukkan alamat email terdaftar Anda, dan kami akan mengirimkan instruksi beserta tautan aman untuk mengatur ulang password akun Anda.
            </p>
            
            <div class="auth-features">
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Tautan Reset Dikirim Langsung ke Email</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Masa Berlaku Token Aman (60 Menit)</span>
                </div>
                <div class="auth-feature-item">
                    <span class="auth-feature-icon"><i class="bi bi-check2"></i></span>
                    <span class="auth-feature-text">Enkripsi Keamanan Tingkat Tinggi</span>
                </div>
            </div>

            <div class="auth-footer-note">
                Ada kendala teknis lainnya? <a href="https://wa.me/#" target="_blank">Hubungi Operator</a>
            </div>
        </div>

        <!-- Right Column: Forgot Password Form Card -->
        <div class="auth-form-col">
            <div class="login-card" style="width: 100%; max-width: 420px;">
                
                <!-- Card Header -->
                <div class="auth-card-header">
                    <h2 class="auth-card-title">Minta Tautan Reset</h2>
                    <p class="auth-card-subtitle">Masukkan email Anda untuk menerima link reset password</p>
                </div>

                <!-- Form Body -->
                <div class="login-form-body" style="padding-top: var(--s-12);">
                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope" style="margin-right:4px;"></i>Alamat Email
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="Masukkan alamat email terdaftar"
                                value="{{ old('email') }}"
                                required
                                autofocus
                            >
                            @error('email')
                                <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-full" style="margin-top: var(--s-8);">
                            <i class="bi bi-send"></i> Kirim Tautan Reset
                        </button>
                    </form>

                    <div style="text-align:center;margin-top:var(--s-20);border-top: 1px dashed var(--outline-variant);padding-top:var(--s-16);">
                        <a href="{{ route('login') }}" style="font-size:13px;font-weight:700;color:var(--primary);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                            <i class="bi bi-arrow-left"></i> Kembali ke Halaman Masuk
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
