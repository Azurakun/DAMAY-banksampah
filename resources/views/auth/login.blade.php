@extends('layouts.app')
@section('title', 'Masuk — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="login-wrapper animate-fade-in">
    <div class="login-card">

        <!-- Hero Section -->
        <div class="login-hero">
            <div class="login-hero-icon">
                <i class="bi bi-recycle"></i>
            </div>
            <h1 class="login-title">EcoBank</h1>
            <p class="login-subtitle">Tabungan Sampah Digital · SMKN 2 Indramayu</p>
        </div>

        <!-- Form Body -->
        <div class="login-form-body">

            <form action="{{ route('login.post') }}" method="POST">
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
                        placeholder="Masukkan alamat email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                    @error('email')
                        <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock" style="margin-right:4px;"></i>Password
                    </label>
                    <div style="position:relative;">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required
                            style="padding-right: 48px;"
                        >
                        <button
                            type="button"
                            id="toggle-pw"
                            onclick="togglePw()"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--on-surface-variant);font-size:16px;padding:4px;"
                            tabindex="-1"
                            aria-label="Tampilkan password"
                        >
                            <i class="bi bi-eye" id="pw-eye"></i>
                        </button>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:var(--s-20);">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;"
                    >
                    <label for="remember" style="font-size:13px;font-weight:600;color:var(--on-surface-variant);cursor:pointer;">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk ke Akun
                </button>
            </form>

            <div style="text-align:center;margin-top:var(--s-16);">
                <span style="font-size:13px;color:var(--on-surface-variant);">Belum punya akun nasabah? </span>
                <a href="{{ route('register') }}" style="font-size:13px;font-weight:700;color:var(--primary);text-decoration:none;">Daftar Sekarang</a>
            </div>

            <!-- Demo Credentials -->
            <div class="demo-creds">
                <p class="demo-creds-title">
                    <i class="bi bi-lightning-charge-fill"></i> Akun Demo — Klik untuk isi otomatis
                </p>
                <div class="demo-grid">
                    <button type="button" class="demo-btn" onclick="fillCred('budi@ecobank.com')">
                        <i class="bi bi-person-fill" style="color:var(--primary);margin-right:4px;"></i>Siswa (Budi)
                    </button>
                    <button type="button" class="demo-btn" onclick="fillCred('agus@ecobank.com')">
                        <i class="bi bi-person-workspace" style="color:var(--teal);margin-right:4px;"></i>Operator
                    </button>
                    <button type="button" class="demo-btn" onclick="fillCred('sri@ecobank.com')">
                        <i class="bi bi-mortarboard-fill" style="color:var(--accent);margin-right:4px;"></i>Wali Kelas
                    </button>
                    <button type="button" class="demo-btn" onclick="fillCred('mulyono@ecobank.com')">
                        <i class="bi bi-shield-fill-check" style="color:var(--danger);margin-right:4px;"></i>Manajer
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function fillCred(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password';
        document.getElementById('email').dispatchEvent(new Event('input'));
    }

    function togglePw() {
        const pw = document.getElementById('password');
        const eye = document.getElementById('pw-eye');
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
