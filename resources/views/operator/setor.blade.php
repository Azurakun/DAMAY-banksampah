@extends('layouts.app')
@section('title', 'Setor Sampah — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    <a href="{{ route('operator.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Dasbor
    </a>

    {{-- Student Info Card --}}
    <div class="balance-card" style="margin-bottom:var(--s-20);">
        <div class="balance-card-inner">
            <div class="balance-label"><i class="bi bi-person" style="margin-right:5px;"></i>Nasabah Setoran</div>
            <div style="font-size:22px;font-weight:800;margin-bottom:var(--s-8);">{{ $student->name }}</div>
            <div class="balance-footer">
                <div class="points-badge">
                    <i class="bi bi-building"></i> {{ $student->class }}
                    &nbsp;·&nbsp;
                    <i class="bi bi-credit-card"></i> {{ $student->nisn }}
                </div>
                <span class="balance-school">
                    Saldo: Rp {{ number_format($student->balance, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Setor Form Card --}}
    <div class="card">
        <h2 style="font-size:17px;font-weight:800;color:var(--on-surface);margin-bottom:var(--s-20);display:flex;align-items:center;gap:var(--s-8);">
            <i class="bi bi-journal-text" style="color:var(--primary);"></i> Catat Setoran Timbangan
        </h2>

        <form action="{{ route('operator.setor.post', $student->id) }}" method="POST">
            @csrf
            <input type="hidden" id="selected_category_id" name="waste_category_id" value="" required>

            {{-- Category Grid --}}
            <div class="form-group">
                <label class="form-label">Pilih Kategori Sampah</label>
                <div class="waste-grid">
                    @foreach($categories as $category)
                        @php
                            $iconMap = [
                                'plastik' => 'bi-cup-straw',
                                'kertas'  => 'bi-box-seam',
                                'logam'   => 'bi-nut',
                                'organik' => 'bi-leaf',
                                'kaca'    => 'bi-gem',
                            ];
                            $colorMap = [
                                'plastik' => 'hsl(200,80%,50%)',
                                'kertas'  => 'hsl(38,80%,50%)',
                                'logam'   => 'hsl(220,30%,50%)',
                                'organik' => 'hsl(130,60%,40%)',
                                'kaca'    => 'hsl(180,60%,45%)',
                            ];
                            $icon  = $iconMap[$category->key]  ?? 'bi-recycle';
                            $color = $colorMap[$category->key] ?? 'var(--primary)';
                        @endphp
                        <div
                            class="waste-item-select"
                            id="cat-{{ $category->id }}"
                            onclick="selectCategory({{ $category->id }}, {{ $category->price_per_kg }}, {{ $category->points_per_kg }}, '{{ $category->name }}')"
                        >
                            <div class="waste-item-icon" style="color:{{ $color }};">
                                <i class="bi {{ $icon }}"></i>
                            </div>
                            <div class="waste-item-name">{{ $category->name }}</div>
                            <div class="waste-item-price">Rp {{ number_format($category->price_per_kg, 0, ',', '.') }} /kg</div>
                        </div>
                    @endforeach
                </div>
                @error('waste_category_id')
                    <span class="form-error"><i class="bi bi-exclamation-circle"></i>Silakan pilih kategori sampah.</span>
                @enderror
            </div>

            {{-- Weight Input --}}
            <div class="form-group">
                <label for="weight" class="form-label">
                    <i class="bi bi-speedometer2" style="margin-right:4px;color:var(--primary);"></i>
                    Berat Timbangan (kg)
                </label>
                <input
                    type="number"
                    step="0.01"
                    id="weight"
                    name="weight"
                    class="form-control"
                    style="font-weight:700;border-width:2px;font-size:18px;height:56px;"
                    placeholder="Pilih kategori terlebih dahulu"
                    required
                    disabled
                >
                @error('weight')
                    <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                @enderror
            </div>

            {{-- Live Calculator Panel --}}
            <div id="calculatorPanel" style="display:none;" class="card" style="background:var(--primary-container);border-color:var(--primary);margin-bottom:var(--s-16);">
                <div style="display:flex;align-items:center;gap:var(--s-8);margin-bottom:var(--s-12);">
                    <i class="bi bi-calculator" style="color:var(--primary);font-size:18px;"></i>
                    <span style="font-size:13px;font-weight:800;color:var(--primary);text-transform:uppercase;letter-spacing:0.4px;">Estimasi Hasil</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--s-12);">
                    <div style="text-align:center;background:var(--surface);border-radius:var(--r-md);padding:var(--s-12);border:1px solid var(--outline-variant);">
                        <div style="font-size:11px;font-weight:700;color:var(--on-surface-variant);text-transform:uppercase;margin-bottom:var(--s-4);">Tambahan Saldo</div>
                        <div id="calcAmount" style="font-size:20px;font-weight:800;color:var(--primary);">Rp 0</div>
                    </div>
                    <div style="text-align:center;background:var(--surface);border-radius:var(--r-md);padding:var(--s-12);border:1px solid var(--outline-variant);">
                        <div style="font-size:11px;font-weight:700;color:var(--on-surface-variant);text-transform:uppercase;margin-bottom:var(--s-4);">Tambahan Poin</div>
                        <div id="calcPoints" style="font-size:20px;font-weight:800;color:var(--accent);">
                            <i class="bi bi-star" style="color:var(--accent);font-size:14px;"></i> 0
                        </div>
                    </div>
                </div>
            </div>

            {{-- Note --}}
            <div class="form-group">
                <label for="note" class="form-label">Catatan Tambahan (Opsional)</label>
                <input type="text" id="note" name="note" class="form-control" placeholder="Contoh: Plastik tebal bersih / kondisi basah sedikit">
                @error('note')
                    <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary w-full" style="height:52px;" disabled>
                <i class="bi bi-save"></i> Simpan Setoran Timbangan
            </button>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
    let activePrice = 0, activePoints = 0;

    function selectCategory(id, price, points, name) {
        document.querySelectorAll('.waste-item-select').forEach(el => el.classList.remove('active'));
        document.getElementById(`cat-${id}`).classList.add('active');
        document.getElementById('selected_category_id').value = id;

        activePrice  = price;
        activePoints = points;

        const w = document.getElementById('weight');
        w.removeAttribute('disabled');
        w.placeholder = 'Masukkan berat dalam kg (contoh: 2.5)';
        w.focus();

        document.getElementById('calculatorPanel').style.display = 'block';
        calculateEstimates();
    }

    document.getElementById('weight').addEventListener('input', calculateEstimates);

    function calculateEstimates() {
        const weight = parseFloat(document.getElementById('weight').value) || 0;
        const amount = Math.round(weight * activePrice);
        const pts    = Math.round(weight * activePoints);

        document.getElementById('calcAmount').textContent  = 'Rp ' + amount.toLocaleString('id-ID');
        document.getElementById('calcPoints').innerHTML    = `<i class="bi bi-star" style="color:var(--accent);font-size:14px;margin-right:3px;"></i>${pts.toLocaleString('id-ID')} Poin`;
        document.getElementById('submitBtn').disabled      = weight <= 0;
    }
</script>
@endsection
