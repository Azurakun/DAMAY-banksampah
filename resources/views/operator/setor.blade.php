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

    {{-- Error display --}}
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:var(--s-16);">
            <span class="alert-icon"><i class="bi bi-exclamation-triangle"></i></span>
            <div>
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('operator.setor.post', $student->id) }}" method="POST" id="setor-form">
        @csrf

        {{-- Setor Form Card --}}
        <div class="card">
            <h2 style="font-size:17px;font-weight:800;color:var(--on-surface);margin-bottom:var(--s-16);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-journal-text" style="color:var(--primary);"></i> Catat Setoran Timbangan
            </h2>

            {{-- Category Picker --}}
            <div class="form-group">
                <label class="form-label">Pilih Kategori Sampah</label>
                <p style="font-size:12px;color:var(--on-surface-variant);margin-top:-6px;margin-bottom:var(--s-12);">
                    <i class="bi bi-info-circle"></i> Klik kategori untuk menambah/menghapus dari daftar setoran. Anda dapat memilih lebih dari satu jenis.
                </p>
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
                            onclick="toggleCategory({{ $category->id }}, {{ $category->price_per_kg }}, {{ $category->points_per_kg }}, '{{ addslashes($category->name) }}')"
                            style="cursor:pointer; position:relative;"
                        >
                            <div class="waste-item-icon" style="color:{{ $color }}; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                @if(Str::startsWith($category->icon, '/uploads/') || Str::startsWith($category->icon, 'http'))
                                    <img src="{{ $category->icon }}" alt="{{ $category->name }}" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;">
                                @else
                                    <i class="bi {{ $icon }}"></i>
                                @endif
                            </div>
                            <div class="waste-item-name">{{ $category->name }}</div>
                            <div class="waste-item-price">Rp {{ number_format($category->price_per_kg, 0, ',', '.') }} /kg</div>
                            {{-- Added badge overlay --}}
                            <div id="badge-{{ $category->id }}" style="display:none; position:absolute; top:6px; right:6px; background:var(--primary); color:white; border-radius:50%; width:22px; height:22px; font-size:11px; font-weight:800; align-items:center; justify-content:center;">
                                <i class="bi bi-check2" style="font-size:12px;"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Cart: List of selected items --}}
            <div id="cart-section" style="display:none; margin-top:var(--s-4); margin-bottom:var(--s-16);">
                <div style="font-size:14px; font-weight:800; color:var(--primary); margin-bottom:var(--s-12); display:flex; align-items:center; gap:6px; border-top: 1px solid var(--outline-variant); padding-top: var(--s-16);">
                    <i class="bi bi-cart3"></i> Daftar Setoran Sampah
                </div>
                <div id="cart-items"></div>
            </div>

            {{-- Summary Panel --}}
            <div id="summary-panel" style="display:none; background:var(--primary-container); border:1px solid var(--primary); border-radius:var(--r-md); padding:var(--s-16); margin-bottom:var(--s-16);">
                <div style="font-size:12px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:.04em; margin-bottom:var(--s-12); display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-calculator"></i> Estimasi Hasil Keseluruhan
                </div>
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:var(--s-8);">
                    <div style="text-align:center; background:var(--surface); border-radius:var(--r-md); padding:var(--s-10); border:1px solid var(--outline-variant);">
                        <div style="font-size:10px; font-weight:700; color:var(--on-surface-variant); text-transform:uppercase; margin-bottom:2px;">Total Berat</div>
                        <div id="sum-weight" style="font-size:16px; font-weight:800; color:var(--on-surface);">0 kg</div>
                    </div>
                    <div style="text-align:center; background:var(--surface); border-radius:var(--r-md); padding:var(--s-10); border:1px solid var(--outline-variant);">
                        <div style="font-size:10px; font-weight:700; color:var(--on-surface-variant); text-transform:uppercase; margin-bottom:2px;">Tambahan Saldo</div>
                        <div id="sum-amount" style="font-size:16px; font-weight:800; color:var(--primary);">Rp 0</div>
                    </div>
                    <div style="text-align:center; background:var(--surface); border-radius:var(--r-md); padding:var(--s-10); border:1px solid var(--outline-variant);">
                        <div style="font-size:10px; font-weight:700; color:var(--on-surface-variant); text-transform:uppercase; margin-bottom:2px;">Tambahan Poin</div>
                        <div id="sum-points" style="font-size:16px; font-weight:800; color:var(--accent);"><i class="bi bi-star" style="font-size:12px;"></i> 0</div>
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

            <div id="items-error" style="display:none;" class="form-error" style="margin-bottom:var(--s-8);">
                <i class="bi bi-exclamation-circle"></i> Harus ada minimal satu jenis sampah.
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary w-full" style="height:52px;" disabled>
                <i class="bi bi-save"></i> Simpan Setoran Timbangan
            </button>
        </div>
    </form>

</div>
@endsection

@section('scripts')
<script>
    // cart[categoryId] = { catId, name, price, points, weight }
    const cart = {};

    function fmt(n) { return n.toLocaleString('id-ID'); }

    function toggleCategory(catId, price, points, name) {
        if (cart[catId]) {
            // It is already in the cart. Remove it!
            removeItem(catId);
        } else {
            // It is not in the cart. Add it!
            cart[catId] = { catId, name, price, points, weight: 0 };
            renderCartRow(catId);
            updateBadge(catId, true);
            updateSummary();
            updateSubmitBtn();
        }
    }

    function renderCartRow(catId) {
        const item = cart[catId];
        const container = document.getElementById('cart-items');

        const row = document.createElement('div');
        row.id = `row-${catId}`;
        row.className = 'cart-item-row';
        
        const catKey = item.name.toLowerCase().includes('plastik') ? 'plastik' :
                       item.name.toLowerCase().includes('kertas') ? 'kertas' :
                       item.name.toLowerCase().includes('logam') ? 'logam' :
                       item.name.toLowerCase().includes('organik') ? 'organik' :
                       item.name.toLowerCase().includes('kaca') ? 'kaca' : '';
        const colorVar = catKey ? `var(--mat-${catKey})` : 'var(--primary)';

        row.innerHTML = `
            <input type="hidden" name="items[${catId}][waste_category_id]" value="${catId}">
            
            <!-- Left/Top section: Material details -->
            <div class="cart-item-info">
                <div class="cart-item-stripe" style="background: ${colorVar};"></div>
                <div class="cart-item-details">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-sub">
                        <span style="font-weight: 700; color: ${colorVar};">Rp ${fmt(item.price)}/kg</span>
                        <span>·</span>
                        <span>⭐ ${item.points} pts/kg</span>
                    </div>
                </div>
            </div>

            <!-- Right/Bottom section: Input & Estimates -->
            <div class="cart-item-row-controls">
                <!-- Weight Input -->
                <div class="cart-item-input-wrapper">
                    <input
                        type="number"
                        id="weight-${catId}"
                        name="items[${catId}][weight]"
                        min="0.01" step="0.01"
                        placeholder="0.00"
                        oninput="updateWeight(${catId})"
                        class="cart-item-input"
                        required
                    >
                    <span class="cart-item-unit">kg</span>
                </div>

                <!-- Live Estimates -->
                <div class="cart-item-estimates">
                    <div id="row-amt-${catId}" class="cart-item-amount">Rp 0</div>
                    <div id="row-pts-${catId}" class="cart-item-points">
                        <i class="bi bi-star-fill" style="font-size: 10px; color:var(--accent);"></i> 0 Poin
                    </div>
                </div>
            </div>
        `;
        container.appendChild(row);
        document.getElementById('cart-section').style.display = 'block';
        document.getElementById('summary-panel').style.display = 'block';
        document.getElementById(`weight-${catId}`).focus();
    }

    function updateWeight(catId) {
        const inp = document.getElementById(`weight-${catId}`);
        const w = parseFloat(inp.value) || 0;
        cart[catId].weight = w;

        const pts = Math.round(w * cart[catId].points);
        const amt = Math.round(w * cart[catId].price);
        
        document.getElementById(`row-amt-${catId}`).textContent = 'Rp ' + amt.toLocaleString('id-ID');
        document.getElementById(`row-pts-${catId}`).innerHTML = `<i class="bi bi-star-fill" style="font-size: 10px; color:var(--accent); margin-right:3px;"></i>${pts.toLocaleString('id-ID')} Poin`;

        updateSummary();
        updateSubmitBtn();
    }

    function removeItem(catId) {
        delete cart[catId];
        const row = document.getElementById(`row-${catId}`);
        if (row) {
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            row.style.transition = 'all 0.2s ease';
            setTimeout(() => row.remove(), 200);
        }
        updateBadge(catId, false);
        document.getElementById(`cat-${catId}`).classList.remove('active');

        if (Object.keys(cart).length === 0) {
            document.getElementById('cart-section').style.display = 'none';
            document.getElementById('summary-panel').style.display = 'none';
        }

        updateSummary();
        updateSubmitBtn();
    }

    function updateBadge(catId, show) {
        const badge = document.getElementById(`badge-${catId}`);
        badge.style.display = show ? 'flex' : 'none';
        const catEl = document.getElementById(`cat-${catId}`);
        if (show) {
            catEl.classList.add('active');
        }
    }

    // Helper to get raw form data on load (pre-populated by old inputs if redirected back)
    document.addEventListener('DOMContentLoaded', () => {
        // Find existing inputs if they exist (old inputs on redirect back)
        const categoriesData = @json($categories);
        categoriesData.forEach(cat => {
            const weightInput = document.querySelector(`input[name="items[${cat.id}][weight]"]`);
            if (weightInput) {
                const w = parseFloat(weightInput.value) || 0;
                cart[cat.id] = { catId: cat.id, name: cat.name, price: cat.price_per_kg, points: cat.points_per_kg, weight: w };
                renderCartRow(cat.id);
                document.getElementById(`weight-${cat.id}`).value = weightInput.value;
                updateWeight(cat.id);
                updateBadge(cat.id, true);
            }
        });
    });

    function updateSummary() {
        let totalWeight = 0, totalAmount = 0, totalPoints = 0;
        Object.values(cart).forEach(item => {
            const w = item.weight || 0;
            totalWeight += w;
            totalAmount += Math.round(w * item.price);
            totalPoints += Math.round(w * item.points);
        });
        document.getElementById('sum-weight').textContent = totalWeight.toFixed(2) + ' kg';
        document.getElementById('sum-amount').textContent = 'Rp ' + fmt(totalAmount);
        document.getElementById('sum-points').innerHTML = `<i class="bi bi-star" style="font-size:12px;"></i> ${fmt(totalPoints)} Poin`;
    }

    function updateSubmitBtn() {
        const hasItems = Object.keys(cart).length > 0;
        const allFilled = hasItems && Object.values(cart).every(item => item.weight > 0);
        document.getElementById('submitBtn').disabled = !allFilled;
        document.getElementById('items-error').style.display = hasItems ? 'none' : 'none';
    }

    // Prevent submit if cart is empty
    document.getElementById('setor-form').addEventListener('submit', function(e) {
        if (Object.keys(cart).length === 0) {
            e.preventDefault();
            document.getElementById('items-error').style.display = 'block';
            return;
        }
        // Check all weights > 0
        const anyEmpty = Object.values(cart).some(item => !item.weight || item.weight <= 0);
        if (anyEmpty) {
            e.preventDefault();
            return;
        }
    });
</script>
<style>
.cart-item-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: var(--s-16);
    align-items: center;
    background: var(--surface);
    border: 1px solid var(--outline-variant);
    border-radius: var(--r-lg);
    padding: var(--s-12) var(--s-16);
    margin-bottom: var(--s-10);
    box-shadow: 0 2px 8px rgba(18,53,38,0.04);
    animation: slide-in 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.cart-item-info {
    display: flex;
    align-items: center;
    gap: var(--s-12);
    min-width: 0;
}

.cart-item-stripe {
    width: 4px;
    height: 36px;
    border-radius: var(--r-sm);
    flex-shrink: 0;
}

.cart-item-details {
    min-width: 0;
}

.cart-item-name {
    font-size: 14px;
    font-weight: 800;
    color: var(--on-surface);
    margin-bottom: 2px;
}

.cart-item-sub {
    font-size: 11px;
    color: var(--on-surface-variant);
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.cart-item-row-controls {
    display: flex;
    align-items: center;
    gap: var(--s-24);
    flex-shrink: 0;
}

.cart-item-input-wrapper {
    position: relative;
    width: 120px;
}

.cart-item-input {
    width: 100%;
    padding: 8px 32px 8px 12px;
    border: 2px solid var(--outline);
    border-radius: var(--r-md);
    font-size: 16px;
    font-weight: 800;
    font-family: var(--font-mono);
    background: var(--surface);
    color: var(--on-surface);
    text-align: right;
}

.cart-item-input:focus {
    border-color: var(--primary);
    outline: none;
}

.cart-item-unit {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    font-weight: 800;
    color: var(--on-surface-variant);
    pointer-events: none;
}

.cart-item-estimates {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    gap: 2px;
    text-align: right;
    width: 110px;
}

.cart-item-amount {
    font-size: 15px;
    font-weight: 800;
    color: var(--primary);
    font-family: var(--font-mono);
}

.cart-item-points {
    font-size: 11px;
    font-weight: 700;
    color: var(--accent);
    display: flex;
    align-items: center;
    gap: 3px;
}

@media (max-width: 576px) {
    .cart-item-row {
        grid-template-columns: 1fr;
        gap: var(--s-12);
        padding: var(--s-16);
    }
    
    .cart-item-info {
        border-bottom: 1px solid var(--outline-variant);
        padding-bottom: var(--s-8);
    }
    
    .cart-item-row-controls {
        justify-content: space-between;
        width: 100%;
        gap: var(--s-16);
    }
    
    .cart-item-input-wrapper {
        width: 130px;
    }
}

@keyframes slide-in {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes pulse-highlight {
    0%   { box-shadow: 0 0 0 0 rgba(var(--primary-rgb, 22,163,74), 0.5); }
    70%  { box-shadow: 0 0 0 8px rgba(var(--primary-rgb, 22,163,74), 0); }
    100% { box-shadow: 0 0 0 0 rgba(var(--primary-rgb, 22,163,74), 0); }
}
</style>
@endsection
