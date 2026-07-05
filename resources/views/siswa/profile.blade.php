@extends('layouts.app')
@section('title', 'Profil Saya — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Breadcrumb / Header --}}
    <div style="margin-bottom: var(--s-24);">
        <a href="{{ route('siswa.dashboard') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:var(--primary); text-decoration:none; margin-bottom: 8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
        <h2 style="font-family:var(--font-display); font-size:26px; font-weight:800; color:var(--primary); margin-top:4px;">Pengaturan Profil</h2>
    </div>

    {{-- Split Layout --}}
    <div class="dashboard-grid">
        
        {{-- Left Column: Identity, Stats & Charts --}}
        <div class="dashboard-column-left">
            
            {{-- Profile Card with Avatar --}}
            <div class="card" style="padding:0; overflow:hidden; border-top: 5px solid var(--primary); margin-bottom: 0;">
                <div class="profile-hero" style="background:linear-gradient(180deg, var(--primary-container) 0%, var(--surface) 100%); padding: var(--s-32) var(--s-24); text-align:center;">
                    <form action="{{ route('siswa.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form-avatar">
                        @csrf
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="phone" value="{{ $user->phone }}">
                        {{-- Avatar container wrapper to allow floating camera icon above frame --}}
                        <div class="profile-avatar-container" onclick="document.getElementById('avatar-input').click()" style="width:100px; height:100px; position:relative; margin:0 auto var(--s-16) auto; cursor:pointer;">
                            <div class="profile-avatar-wrap" style="width:100%; height:100%; border-radius:50%; overflow:hidden; border:3px solid white; box-shadow:var(--shadow-sm); display:flex; align-items:center; justify-content:center; background:var(--primary); position:relative;">
                                @if($user->avatar)
                                    <img id="avatar-preview" src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width:100%; height:100%; object-fit:cover;">
                                @else
                                    <i id="avatar-placeholder" class="bi bi-person-fill" style="color:white; font-size:54px;"></i>
                                    <img id="avatar-preview" src="" style="display:none; width:100%; height:100%; object-fit:cover;" alt="">
                                @endif
                            </div>
                            {{-- Camera button placed outside the overflow:hidden wrapper to prevent clipping --}}
                            <div class="profile-camera-btn" style="position:absolute; bottom:0; right:0; z-index:10; background:var(--accent); color:var(--on-accent); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid white; box-shadow:var(--shadow-sm);">
                                <i class="bi bi-camera-fill" style="font-size:14px; color:white;"></i>
                            </div>
                        </div>

                        <input type="file" id="avatar-input" name="avatar" style="display:none;" accept="image/*" onchange="previewImage(this)">
                        @error('avatar')
                            <span class="form-error" style="color:var(--danger); font-size:12px; justify-content:center; margin-top:var(--s-6); display:block;">{{ $message }}</span>
                        @enderror
                    </form>

                    <h3 style="font-family:var(--font-display); font-size:20px; font-weight:800; color:var(--on-surface);">{{ $user->name }}</h3>
                    <div style="margin-top:6px;">
                        <span class="badge badge-primary" style="background:var(--primary); color:white; padding: 4px 12px; border-radius:var(--r-full); font-size:11px; font-weight:700;">Siswa · {{ $user->class }}</span>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div style="display:grid; grid-template-columns:repeat(3,1fr); border-top:1px solid var(--outline-variant); border-bottom:1px solid var(--outline-variant);">
                    <div style="text-align:center; padding:var(--s-16) var(--s-8); border-right:1px solid var(--outline-variant);">
                        <div style="font-size:15px; font-weight:800; color:var(--primary);">Rp {{ number_format($user->balance, 0, ',', '.') }}</div>
                        <div style="font-size:10.5px; font-weight:600; color:var(--on-surface-variant); margin-top:2px;">Saldo</div>
                    </div>
                    <div style="text-align:center; padding:var(--s-16) var(--s-8); border-right:1px solid var(--outline-variant);">
                        <div style="font-size:15px; font-weight:800; color:var(--accent);">{{ number_format($user->points, 0, ',', '.') }}</div>
                        <div style="font-size:10.5px; font-weight:600; color:var(--on-surface-variant); margin-top:2px;">Poin</div>
                    </div>
                    <div style="text-align:center; padding:var(--s-16) var(--s-8);">
                        <div style="font-size:15px; font-weight:800; color:var(--teal);">{{ number_format($totalWeight ?? 0, 1, ',', '.') }} kg</div>
                        <div style="font-size:10.5px; font-weight:600; color:var(--on-surface-variant); margin-top:2px;">Disetor</div>
                    </div>
                </div>

                {{-- Chart Section --}}
                <div style="padding: var(--s-24) var(--s-28);">
                    <h4 style="font-size:13.5px; font-weight:800; color:var(--on-surface); margin-bottom:var(--s-16); text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:6px;">
                        <i class="bi bi-graph-up-arrow" style="color:var(--primary);"></i> Riwayat Setoran (6 Bulan Terakhir)
                    </h4>
                    <div style="position:relative; height:180px; width:100%;">
                        <canvas id="tabunganChart"></canvas>
                    </div>
                </div>

                {{-- Account Metadata --}}
                <div style="padding: var(--s-20) var(--s-28); border-top: 1px dashed var(--outline-variant); background: var(--surface-dim);">
                    <div class="info-grid" style="display:flex; flex-direction:column; gap:var(--s-8);">
                        <div style="display:flex; justify-content:space-between; font-size:13px;">
                            <span style="color:var(--on-surface-variant); font-weight:600;"><i class="bi bi-credit-card" style="margin-right:6px;"></i> NISN</span>
                            <strong style="color:var(--on-surface); font-family:var(--font-mono);">{{ $user->nisn }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px;">
                            <span style="color:var(--on-surface-variant); font-weight:600;"><i class="bi bi-building" style="margin-right:6px;"></i> Kelas</span>
                            <strong style="color:var(--on-surface);">{{ $user->class }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px;">
                            <span style="color:var(--on-surface-variant); font-weight:600;"><i class="bi bi-envelope" style="margin-right:6px;"></i> Email</span>
                            <strong style="color:var(--on-surface); word-break:break-all;">{{ $user->email }}</strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Edit Profile Form --}}
        <div class="dashboard-column-right">
            <div class="card" style="padding: var(--s-32) var(--s-28); border-top: 5px solid var(--accent);">
                <h3 style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--on-surface); margin-bottom:var(--s-24); display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-pencil-square" style="color:var(--accent);"></i> Ubah Detail Profil
                </h3>

                <form action="{{ route('siswa.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:6px; display:block;"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890">
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

    <!-- Modal Crop Avatar -->
    <div id="cropModal" style="display:none; position:fixed; z-index:10000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center; opacity:0; transition:opacity 0.25s ease; padding: 16px;">
        <div class="card" style="background:var(--surface); max-width:400px; width:100%; border-radius:var(--r-lg); border:1px solid var(--outline-variant); box-shadow:var(--shadow-xl); padding:var(--s-20); display:flex; flex-direction:column; gap:16px; margin:auto; transform:translateY(-30px); transition:transform 0.25s ease; border-top: 5px solid var(--primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--outline-variant); padding-bottom:10px;">
                <h3 style="font-family:var(--font-display); font-size:15px; font-weight:800; color:var(--primary); margin:0; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-crop"></i> Potong Foto Profil
                </h3>
                <button type="button" onclick="closeCropModal()" style="background:none; border:none; font-size:24px; color:var(--on-surface-variant); cursor:pointer; line-height:1; padding:0;">&times;</button>
            </div>
            
            <div style="width:100%; max-height:280px; overflow:hidden; border-radius:var(--r-md); background:#000; display:flex; align-items:center; justify-content:center;">
                <img id="cropper-image" src="" style="max-width:100%; max-height:280px; display:block;">
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--outline-variant); padding-top:14px; margin-top:4px;">
                <button type="button" class="btn btn-outline" onclick="closeCropModal()" style="height:36px; padding:0 var(--s-16); font-size:13px; border-radius:var(--r-sm); border:1.5px solid var(--outline-variant); background:transparent; color:var(--on-surface-variant); cursor:pointer;">Batal</button>
                <button type="button" id="crop-save-btn" class="btn btn-primary" style="height:36px; padding:0 var(--s-16); font-size:13px; border-radius:var(--r-sm); border:none; background:var(--primary); color:white; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <i class="bi bi-check-lg"></i> Potong & Simpan
                </button>
            </div>
        </div>
    </div>

    <style>
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }
        .cropper-line, .cropper-point {
            background-color: var(--primary);
        }
        .cropper-bg {
            background-image: none;
            background-color: #1a1a1a;
        }
    </style>

    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let cropper = null;

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Check if file is an image
            if (!file.type.startsWith('image/')) {
                alert('Silakan pilih file gambar yang valid.');
                return;
            }

            const reader = new FileReader();
            reader.onload = e => {
                const cropperImage = document.getElementById('cropper-image');
                cropperImage.src = e.target.result;
                
                openCropModal();
            };
            reader.readAsDataURL(file);
        }
    }

    function openCropModal() {
        const modal = document.getElementById('cropModal');
        if (modal) {
            modal.style.display = 'flex';
            modal.offsetHeight; // Force reflow
            modal.style.opacity = '1';
            modal.querySelector('.card').style.transform = 'translateY(0)';
            
            // Initialize Cropper.js
            const image = document.getElementById('cropper-image');
            if (cropper) {
                cropper.destroy();
            }
            
            cropper = new Cropper(image, {
                aspectRatio: 1, // 1:1 Aspect Ratio
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: false,
                center: false,
                highlight: false,
                cropBoxMovable: false,
                cropBoxResizable: false,
                toggleDragModeOnDblclick: false,
            });
        }
    }

    function closeCropModal() {
        const modal = document.getElementById('cropModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('.card').style.transform = 'translateY(-30px)';
            setTimeout(() => {
                modal.style.display = 'none';
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                // Clear file input
                document.getElementById('avatar-input').value = '';
            }, 250);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Handle Crop Save Button
        const cropSaveBtn = document.getElementById('crop-save-btn');
        if (cropSaveBtn) {
            cropSaveBtn.addEventListener('click', function() {
                if (!cropper) return;
                
                // Show loading state
                const saveBtn = document.getElementById('crop-save-btn');
                const originalHTML = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

                // Get cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });

                canvas.toBlob(function(blob) {
                    if (blob) {
                        // Create file from blob
                        const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                        
                        // Inject file into input element
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        document.getElementById('avatar-input').files = dataTransfer.files;
                        
                        // Show preview immediately in page
                        const preview = document.getElementById('avatar-preview');
                        const placeholder = document.getElementById('avatar-placeholder');
                        if (preview) {
                            preview.src = canvas.toDataURL('image/jpeg');
                            preview.style.display = 'block';
                        }
                        if (placeholder) placeholder.style.display = 'none';

                        // Submit form
                        document.getElementById('profile-form-avatar').submit();
                    } else {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalHTML;
                        alert('Gagal memproses pemotongan gambar.');
                    }
                }, 'image/jpeg', 0.9);
            });
        }

        // Close modal if user clicks outside of modal container
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('cropModal');
            if (modal && e.target === modal) {
                closeCropModal();
            }
        });

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
                    borderColor: '#123526', // Eco-green
                    backgroundColor: 'rgba(18, 53, 38, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#B8792B', // Gold Stamp
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
                        grid: {
                            color: 'rgba(18, 53, 38, 0.05)'
                        },
                        ticks: {
                            callback: function(value) { return 'Rp ' + (value/1000) + 'k'; },
                            color: '#55594E'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#55594E' }
                    }
                }
            }
        });
    });
</script>
@endsection
