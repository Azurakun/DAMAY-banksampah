@extends('layouts.app')
@section('title', 'Profil Saya — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="max-width:520px;margin:0 auto;">

    <a href="{{ route('siswa.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Beranda
    </a>

    <div class="card" style="padding:0;overflow:hidden;">

        {{-- Profile Hero --}}
        <div class="profile-hero" style="background:linear-gradient(180deg, var(--primary-container) 0%, var(--surface) 100%);">
            <form action="{{ route('siswa.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
                @csrf

                <div class="profile-avatar-wrap" onclick="document.getElementById('avatar-input').click()">
                    <div class="profile-avatar-circle">
                        @if($user->avatar)
                            <img id="avatar-preview" src="{{ $user->avatar }}" alt="{{ $user->name }}">
                        @else
                            <i id="avatar-placeholder" class="bi bi-person-fill" style="color:white;font-size:50px;"></i>
                            <img id="avatar-preview" src="" style="display:none;width:100%;height:100%;object-fit:cover;" alt="">
                        @endif
                    </div>
                    <div class="profile-camera-btn">
                        <i class="bi bi-camera-fill"></i>
                    </div>
                </div>
                <input type="file" id="avatar-input" name="avatar" style="display:none;" accept="image/*" onchange="previewImage(this)">
                @error('avatar')
                    <span class="form-error" style="justify-content:center;margin-top:var(--s-6);">{{ $message }}</span>
                @enderror

                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-role">Siswa · {{ $user->class }}</div>

            {{-- Stats row --}}
            </form>
        </div>

        {{-- Stats Chips --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--outline-variant);border-bottom:1px solid var(--outline-variant);">
            <div style="text-align:center;padding:var(--s-16) var(--s-8);border-right:1px solid var(--outline-variant);">
                <div style="font-size:16px;font-weight:800;color:var(--primary);">Rp {{ number_format($user->balance, 0, ',', '.') }}</div>
                <div style="font-size:10.5px;font-weight:600;color:var(--on-surface-variant);margin-top:2px;">Saldo</div>
            </div>
            <div style="text-align:center;padding:var(--s-16) var(--s-8);border-right:1px solid var(--outline-variant);">
                <div style="font-size:16px;font-weight:800;color:var(--accent);">{{ number_format($user->points, 0, ',', '.') }}</div>
                <div style="font-size:10.5px;font-weight:600;color:var(--on-surface-variant);margin-top:2px;">Poin</div>
            </div>
            <div style="text-align:center;padding:var(--s-16) var(--s-8);">
                <div style="font-size:16px;font-weight:800;color:var(--teal);">{{ number_format($totalWeight ?? 0, 1, ',', '.') }} kg</div>
                <div style="font-size:10.5px;font-weight:600;color:var(--on-surface-variant);margin-top:2px;">Disetor</div>
            </div>
        </div>

        {{-- Form Body --}}
        <div style="padding:var(--s-20);">

            {{-- Chart.js Tabungan --}}
            <div style="margin-bottom:var(--s-24);">
                <h3 style="font-size:14px;font-weight:700;color:var(--on-surface);margin-bottom:12px;"><i class="bi bi-graph-up-arrow" style="margin-right:6px;color:var(--primary);"></i>Riwayat Setoran (6 Bulan Terakhir)</h3>
                <div style="position:relative;height:200px;width:100%;">
                    <canvas id="tabunganChart"></canvas>
                </div>
            </div>

            {{-- Read-only Identity --}}
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-credit-card" style="margin-right:4px;"></i>NISN</span>
                    <span class="info-value">{{ $user->nisn }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-building" style="margin-right:4px;"></i>Kelas</span>
                    <span class="info-value">{{ $user->class }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-envelope" style="margin-right:4px;"></i>Email</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
            </div>

            {{-- Editable Fields --}}
            <form action="{{ route('siswa.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Nomor Telepon / WhatsApp</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890">
                    @error('phone')
                        <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="bi bi-check-lg"></i> Simpan Perubahan
                </button>

            </form>

            <div class="divider-dashed" style="margin-top:var(--s-20);"></div>

            <form action="{{ route('logout') }}" method="POST" style="margin-top:var(--s-12);">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-full">
                    <i class="bi bi-box-arrow-right"></i> Keluar dari Akun
                </button>
            </form>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const preview = document.getElementById('avatar-preview');
                const placeholder = document.getElementById('avatar-placeholder');
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('tabunganChart').getContext('2d');
        const labels = {!! json_encode($labels ?? []) !!};
        const data = {!! json_encode($data ?? []) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Setoran (Rp)',
                    data: data,
                    borderColor: '#059669', // Eco-green
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#059669',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return 'Rp ' + (value/1000) + 'k'; }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endsection
