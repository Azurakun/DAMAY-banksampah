@extends('layouts.app')
@section('title', 'Tambah Kategori Sampah — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Breadcrumb --}}
    <div style="margin-bottom: var(--s-24);">
        <a href="{{ route('manajer.categories.index') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:var(--primary); text-decoration:none; margin-bottom: 8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Kategori
        </a>
        <h2 style="font-family:var(--font-display); font-size:26px; font-weight:800; color:var(--primary); margin-top:4px;">Tambah Kategori Sampah Baru</h2>
    </div>

    <style>
        .category-settings-grid {
            display: grid;
            grid-template-columns: 1fr 1.8fr;
            gap: var(--s-24);
            align-items: start;
        }
        @media (max-width: 768px) {
            .category-settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- Layout Grid --}}
    <div class="category-settings-grid">
        
        {{-- Left: Live Preview Card --}}
        <div class="card" style="border-top: 5px solid var(--accent); padding: var(--s-24); text-align: center;">
            <h3 style="font-family:var(--font-display); font-size:15px; font-weight:800; color:var(--primary); margin-bottom:var(--s-20); border-bottom:1px dashed var(--outline); padding-bottom:8px;">
                Pratinjau Kategori Baru
            </h3>

            {{-- Simulated Operator Item Card --}}
            <div style="background:var(--surface-dim); border: 2px solid var(--outline-variant); border-radius:var(--r-lg); padding: var(--s-24) var(--s-16); max-width: 240px; margin: 0 auto; box-shadow: var(--shadow-sm);">
                {{-- Category Icon Container --}}
                <div id="preview-icon-box" style="width:64px; height:64px; border-radius:50%; background:var(--surface); margin:0 auto var(--s-12) auto; display:flex; align-items:center; justify-content:center; box-shadow:var(--shadow-sm); border:1px solid var(--outline-variant); overflow:hidden;">
                    <img id="preview-icon-img" style="width:100%; height:100%; object-fit:cover; display:none;">
                    <span id="preview-icon-emoji" style="font-size:28px;">🥤</span>
                </div>

                <div style="font-family:var(--font-display); font-size:16px; font-weight:800; color:var(--on-surface);" id="preview-name">
                    Nama Kategori
                </div>
                
                <div style="font-size:11px; font-family:var(--font-mono); color:var(--on-surface-variant); font-weight:700; margin-top:2px;" id="preview-key">
                    key: baru
                </div>

                <div style="margin-top:var(--s-16); border-top:1px dashed var(--outline-variant); padding-top:var(--s-12); display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div>
                        <div style="font-size:9px; font-weight:600; color:var(--on-surface-variant); text-transform:uppercase;">Harga Beli</div>
                        <span style="font-family:var(--font-mono); font-weight:800; font-size:13px; color:var(--primary);" id="preview-price">
                            Rp 0
                        </span>
                    </div>
                    <div>
                        <div style="font-size:9px; font-weight:600; color:var(--on-surface-variant); text-transform:uppercase;">Poin Reward</div>
                        <span style="font-family:var(--font-mono); font-weight:800; font-size:13px; color:var(--accent);" id="preview-points">
                            0 Pts
                        </span>
                    </div>
                </div>
            </div>
            
            <p style="font-size:12px; color:var(--on-surface-variant); margin-top:var(--s-16); line-height:1.4;">
                Ini adalah visualisasi tampilan kartu kategori yang akan dilihat oleh Petugas Operator pada menu timbangan setoran nasabah.
            </p>
        </div>

        {{-- Right: Form Card --}}
        <div class="card" style="border-top: 5px solid var(--primary); padding: var(--s-28);">
            <form action="{{ route('manajer.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--s-16);">
                    {{-- Category Name --}}
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Kategori Sampah</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Botol Plastik PET" oninput="updatePreviewName(this.value)">
                        @error('name')
                            <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Key --}}
                    <div class="form-group">
                        <label for="key" class="form-label">Slug / Key (Pengenal Unik)</label>
                        <input type="text" id="key" name="key" class="form-control" value="{{ old('key') }}" required placeholder="Contoh: plastik_botol" oninput="updatePreviewKey(this.value)">
                        @error('key')
                            <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--s-16); margin-top:var(--s-16);">
                    {{-- Price Beli --}}
                    <div class="form-group">
                        <label for="price_per_kg" class="form-label">Harga Beli Nasabah (Rp / Kg)</label>
                        <input type="number" min="0" id="price_per_kg" name="price_per_kg" class="form-control" value="{{ old('price_per_kg', 0) }}" required style="font-family:var(--font-mono);" oninput="updatePreviewPrice(this.value)">
                        @error('price_per_kg')
                            <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Poin Reward --}}
                    <div class="form-group">
                        <label for="points_per_kg" class="form-label">Poin Reward (per Kg)</label>
                        <input type="number" min="0" id="points_per_kg" name="points_per_kg" class="form-control" value="{{ old('points_per_kg', 0) }}" required style="font-family:var(--font-mono);" oninput="updatePreviewPoints(this.value)">
                        @error('points_per_kg')
                            <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Image File Upload --}}
                <div class="form-group" style="margin-top:var(--s-16); border:1px dashed var(--outline-variant); padding:var(--s-16); border-radius:var(--r-md); background:var(--surface-dim);">
                    <label for="icon_image" class="form-label" style="font-weight:700; color:var(--primary); display:flex; align-items:center; gap:6px;">
                        <i class="bi bi-image" style="font-size:16px;"></i> Unggah Gambar Ikon (Direkomendasikan)
                    </label>
                    <input type="file" id="icon_image" name="icon_image" class="form-control" accept="image/*" onchange="previewUploadedImage(this)" style="padding:6px 12px; font-size:12px; margin-top:4px; background:white;">
                    <span style="font-size:11px; color:var(--on-surface-variant); display:block; margin-top:6px; line-height:1.3;">
                        Format gambar: JPG, PNG, WEBP, atau SVG. Ukuran maks 2MB. Jika diunggah, ikon emoji teks di bawah ini akan diabaikan.
                    </span>
                    @error('icon_image')
                        <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Emoji Text Input fallback --}}
                <div class="form-group" style="margin-top:var(--s-16);">
                    <label for="icon" class="form-label">Atau Gunakan Emoji / Teks Ikon</label>
                    <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', '🥤') }}" placeholder="Contoh: 🥤, 📦, 🥫, atau leaf" oninput="updatePreviewEmoji(this.value)">
                    <span style="font-size:11px; color:var(--on-surface-variant); display:block; margin-top:4px;">Isi jika Anda tidak ingin menggunakan gambar ikon unggahan.</span>
                    @error('icon')
                        <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div style="margin-top: var(--s-32); display:flex; gap:var(--s-12); justify-content:flex-end; border-top:1px solid var(--outline-variant); padding-top:var(--s-20);">
                    <a href="{{ route('manajer.categories.index') }}" class="btn btn-outline" style="border:1.5px solid var(--outline-variant); color:var(--on-background); font-weight:700; text-decoration:none; padding:8px 18px; border-radius:var(--r-md); font-size:13px;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary); color:white; font-weight:700; padding:8px 24px; border-radius:var(--r-md); font-size:13px; box-shadow:var(--shadow-sm);">
                        <i class="bi bi-plus-lg"></i> Simpan Kategori
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function updatePreviewName(val) {
        document.getElementById('preview-name').innerText = val || 'Nama Kategori';
    }

    function updatePreviewKey(val) {
        document.getElementById('preview-key').innerText = 'key: ' + (val.toLowerCase().replace(/[^a-z0-9_]/g, '') || 'key');
    }

    function updatePreviewPrice(val) {
        const num = parseFloat(val) || 0;
        document.getElementById('preview-price').innerText = 'Rp ' + num.toLocaleString('id-ID');
    }

    function updatePreviewPoints(val) {
        const num = parseFloat(val) || 0;
        document.getElementById('preview-points').innerText = num.toLocaleString('id-ID') + ' Pts';
    }

    function updatePreviewEmoji(val) {
        const emojiSpan = document.getElementById('preview-icon-emoji');
        const img = document.getElementById('preview-icon-img');
        
        if (val.trim() !== '') {
            emojiSpan.innerText = val;
            emojiSpan.style.display = 'block';
            img.style.display = 'none';
        }
    }

    function previewUploadedImage(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('preview-icon-img');
                const emojiSpan = document.getElementById('preview-icon-emoji');
                
                img.src = e.target.result;
                img.style.display = 'block';
                emojiSpan.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection
