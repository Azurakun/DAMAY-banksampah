@extends('layouts.app')
@section('title', 'Daftar Akun Nasabah — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="login-wrapper animate-fade-in" style="margin-bottom:var(--s-32);">
    <div class="login-card" style="max-width:480px;">

        <!-- Hero Section -->
        <div class="login-hero">
            <div class="login-hero-icon">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h1 class="login-title">Daftar Akun</h1>
            <p class="login-subtitle">Bergabung sebagai Nasabah Bank Sampah</p>
        </div>

        <!-- Form Body -->
        <div class="login-form-body">
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
                        <label for="class" class="form-label">
                            <i class="bi bi-building" style="margin-right:4px;"></i>Kelas
                        </label>
                        <input
                            type="text"
                            id="class"
                            name="class"
                            class="form-control"
                            placeholder="Contoh: XII RPL 1"
                            value="{{ old('class') }}"
                            required
                        >
                        @error('class')
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
@endsection
