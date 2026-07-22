@extends('layouts.app')
@section('title', 'Catat Distribusi Baru — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    <style>
        /* Default Column Sizing on Desktop */
        .col-pilih { width: 50px; text-align: center; }
        .col-berat { width: 110px; }
        .col-harga { width: 130px; }
        .col-subtotal { width: 110px; text-align: right; }
        
        @media (max-width: 600px) {
            /* Reduce page wrapper padding on mobile but keep breathing room */
            .card {
                padding: var(--s-20) var(--s-16) !important;
            }

            /* Condense Table Layout */
            .data-table {
                font-size: 13px !important;
                min-width: 480px !important; /* Ensure table has enough room and scrolls cleanly on small viewports */
            }
            .data-table thead th, 
            .data-table tbody td {
                padding: 10px 8px !important; /* Elegant spacing that is not too cramped */
            }
            .col-pilih { width: 36px !important; text-align: center !important; }
            .col-berat { width: 80px !important; }
            .col-harga { width: 90px !important; }
            .col-subtotal { width: 85px !important; text-align: right !important; }
            
            .cat-weight, .cat-price {
                padding: 4px 6px !important;
                font-size: 12px !important;
                height: 30px !important;
            }
            
            /* Hide category icon wrapper on mobile */
            .cat-icon-col {
                display: none !important;
            }
        }
    </style>

    {{-- Breadcrumb --}}
    <div style="margin-bottom: var(--s-24);">
        <a href="{{ route(Auth::user()->role . '.distributions.index') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:var(--primary); text-decoration:none; margin-bottom: 8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Riwayat Distribusi
        </a>
        <h2 style="font-family:var(--font-display); font-size:26px; font-weight:800; color:var(--primary); margin-top:4px;">Catat Distribusi Sampah Baru</h2>
    </div>

    <div class="card" style="padding: var(--s-32) var(--s-28); border-top: 5px solid var(--primary);">
        <form action="{{ route(Auth::user()->role . '.distributions.store') }}" method="POST" id="dist-form">
            @csrf

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--s-20);">
                
                {{-- Date --}}
                <div class="form-group">
                    <label for="batch_date" class="form-label">Tanggal Batch Distribusi</label>
                    <input type="date" id="batch_date" name="batch_date" class="form-control" value="{{ old('batch_date', date('Y-m-d')) }}" required>
                    @error('batch_date')
                        <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Route --}}
                <div class="form-group">
                    <label for="route" class="form-label">Jalur Distribusi</label>
                    <select id="route" name="route" class="form-control" required onchange="toggleRouteFields(this.value)">
                        <option value="agent" {{ old('route') == 'agent' ? 'selected' : '' }}>Jual ke Agen Pembeli (Menghasilkan Kas Masuk)</option>
                        <option value="unit" {{ old('route') == 'unit' ? 'selected' : '' }}>Unit Pengolahan Internal Sekolah (Rp 0 / Tanpa Kas)</option>
                    </select>
                    @error('route')
                        <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Agent Name (shows only for route == agent) --}}
            <div class="form-group" id="agent-group" style="margin-top: var(--s-16);">
                <label for="agent_name" class="form-label">Nama Agen Pembeli</label>
                <input type="text" id="agent_name" name="agent_name" class="form-control" value="{{ old('agent_name') }}" placeholder="Contoh: CV Daur Mulia Indramayu">
                @error('agent_name')
                    <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Notes --}}
            <div class="form-group" style="margin-top: var(--s-16);">
                <label for="notes" class="form-label">Catatan Tambahan (Opsional)</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Tulis catatan penting mengenai batch pengeluaran sampah ini...">{{ old('notes') }}</textarea>
                @error('notes')
                    <span class="form-error" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- Grid selection of waste categories to distribute --}}
            <div style="margin-top: var(--s-28); border-top: 1px dashed var(--outline); padding-top: var(--s-20);">
                <h3 style="font-family:var(--font-display); font-size:16px; font-weight:800; color:var(--primary); margin-bottom:var(--s-16); display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-recycle" style="color:var(--accent);"></i> Rincian Timbangan Sampah Keluar
                </h3>

                <p style="font-size:13px; color:var(--on-surface-variant); margin-bottom:var(--s-16);">
                    Pilih jenis sampah yang ingin dikeluarkan dan masukkan beratnya. Stok tidak boleh melebihi persediaan gudang saat ini.
                </p>

                <div class="table-overflow" style="border: 1px solid var(--outline-variant); border-radius:var(--r-md); overflow: auto; margin-top:var(--s-12);">
                    <table class="data-table" style="margin:0;">
                        <thead>
                            <tr>
                                <th class="col-pilih">Pilih</th>
                                <th>Jenis Sampah</th>
                                <th class="col-berat">Berat Keluar (Kg)</th>
                                <th class="col-harga val-field">Harga Jual (Rp/Kg)</th>
                                <th class="col-subtotal">Nilai Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $index => $cat)
                                @php
                                    $available = $cat->available_stock;
                                @endphp
                                <tr id="row_{{ $cat->id }}" style="transition: all 0.25s ease; background: var(--surface-dim); opacity: 0.65;">
                                    {{-- Checkbox --}}
                                    <td class="col-pilih" style="vertical-align:middle;">
                                        <input type="checkbox" id="cat_{{ $cat->id }}" class="cat-checkbox" onchange="toggleCategoryInput({{ $cat->id }})" style="width:18px; height:18px; accent-color:var(--primary); cursor:pointer;">
                                    </td>

                                    {{-- Info --}}
                                    <td style="vertical-align:middle;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:20px; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; overflow:hidden;" class="cat-icon-col">
                                                @if(Str::startsWith($cat->icon, '/uploads/') || Str::startsWith($cat->icon, 'http'))
                                                    <img src="{{ $cat->icon }}" alt="{{ $cat->name }}" style="width: 24px; height: 24px; object-fit: cover; border-radius: 4px;">
                                                @else
                                                    {{ $cat->icon }}
                                                @endif
                                            </span>
                                            <div>
                                                <strong style="color:var(--on-surface); font-size:14px; display:block;">{{ $cat->name }}</strong>
                                                <span style="font-size:11px; color:var(--on-surface-variant); font-weight:600;">
                                                    Stok Gudang: <span style="font-family:var(--font-mono); font-weight:800; color:var(--primary);">{{ number_format($available, 2, ',', '.') }} kg</span>
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Weight Input --}}
                                    <td class="col-berat" style="vertical-align:middle;">
                                        <input type="number" step="0.01" min="0.01" max="{{ $available }}" id="weight_{{ $cat->id }}" name="items[{{ $index }}][weight]" class="form-control cat-weight" disabled oninput="calculateItemTotal({{ $cat->id }}, {{ $cat->price_per_kg }})" placeholder="0.00" style="padding: 6px 10px; font-size:13px; font-family:var(--font-mono); margin:0;">
                                        <input type="hidden" name="items[{{ $index }}][waste_category_id]" value="{{ $cat->id }}" id="cat_id_{{ $cat->id }}" disabled>
                                    </td>

                                    {{-- Price Input --}}
                                    <td class="col-harga val-field" style="vertical-align:middle;">
                                        <input type="number" min="0" id="price_{{ $cat->id }}" name="items[{{ $index }}][price_per_kg]" class="form-control cat-price" value="{{ $cat->price_per_kg }}" disabled oninput="calculateItemTotal({{ $cat->id }}, this.value)" style="padding: 6px 10px; font-size:13px; font-family:var(--font-mono); margin:0;">
                                    </td>

                                    {{-- Subtotal Output --}}
                                    <td class="col-subtotal" style="vertical-align:middle; font-weight:700; font-family:var(--font-mono); font-size:14px; color:var(--primary);" id="subtotal_{{ $cat->id }}">
                                        Rp 0
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Grand Totals --}}
            <div style="margin-top: var(--s-24); background:var(--surface-container); border-radius:var(--r-md); padding:var(--s-16) var(--s-20); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:12px; color:var(--on-surface-variant); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Akumulasi Total</span>
                    <div style="font-size:13px; font-weight:700; color:var(--on-surface); margin-top:2px;">
                        Total Berat: <span id="grand-weight" style="font-family:var(--font-mono); font-weight:800; color:var(--primary);">0.00 kg</span>
                    </div>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:12px; color:var(--on-surface-variant); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;" class="val-label">Total Nilai Penjualan</span>
                    <div style="font-family:var(--font-mono); font-size:20px; font-weight:800; color:var(--accent);" id="grand-total" class="val-amount">Rp 0</div>
                </div>
            </div>

            {{-- Action buttons --}}
            <div style="margin-top: var(--s-32); display:flex; gap:var(--s-12); justify-content:flex-end;">
                <a href="{{ route(Auth::user()->role . '.distributions.index') }}" class="btn btn-outline" style="border:1.5px solid var(--outline-variant); color:var(--on-background); font-weight:700; text-decoration:none; padding:10px 20px; border-radius:var(--r-md);">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary); color:white; font-weight:700; padding:10px 24px; border-radius:var(--r-md); box-shadow:var(--shadow-stamp);">
                    <i class="bi bi-check-lg"></i> Simpan Distribusi
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleRouteFields(route) {
        const agentGroup = document.getElementById('agent-group');
        const agentNameInput = document.getElementById('agent_name');
        const valFields = document.querySelectorAll('.val-field');
        const valLabels = document.querySelectorAll('.val-label');
        const valAmounts = document.querySelectorAll('.val-amount');

        if (route === 'unit') {
            agentGroup.style.display = 'none';
            agentNameInput.required = false;
            agentNameInput.value = '';
            
            // Hide pricing columns
            valFields.forEach(f => f.style.display = 'none');
            valLabels.forEach(l => l.style.visibility = 'hidden');
            valAmounts.forEach(a => a.style.visibility = 'hidden');
        } else {
            agentGroup.style.display = 'block';
            agentNameInput.required = true;
            
            // Show pricing columns
            valFields.forEach(f => f.style.display = 'table-cell');
            valLabels.forEach(l => l.style.visibility = 'visible');
            valAmounts.forEach(a => a.style.visibility = 'visible');
        }
        calculateGrandTotal();
    }

    function toggleCategoryInput(id) {
        const checkbox = document.getElementById('cat_' + id);
        const weightInput = document.getElementById('weight_' + id);
        const priceInput = document.getElementById('price_' + id);
        const subtotalSpan = document.getElementById('subtotal_' + id);
        const row = document.getElementById('row_' + id);
        const hiddenIdInput = document.getElementById('cat_id_' + id);

        if (checkbox.checked) {
            weightInput.disabled = false;
            priceInput.disabled = false;
            if (hiddenIdInput) hiddenIdInput.disabled = false;
            weightInput.required = true;
            if (document.getElementById('route').value === 'agent') {
                priceInput.required = true;
            }
            
            // Highlight selected row
            row.style.background = 'rgba(63, 125, 74, 0.08)'; // soft eco green
            row.style.opacity = '1';
        } else {
            weightInput.disabled = true;
            priceInput.disabled = true;
            if (hiddenIdInput) hiddenIdInput.disabled = true;
            weightInput.required = false;
            priceInput.required = false;
            weightInput.value = '';
            subtotalSpan.innerText = 'Rp 0';
            
            // Un-highlight unselected row
            row.style.background = 'var(--surface-dim)';
            row.style.opacity = '0.65';
        }
        calculateGrandTotal();
    }

    function calculateItemTotal(id, price) {
        const weight = parseFloat(document.getElementById('weight_' + id).value) || 0;
        const rate = parseFloat(price) || 0;
        const subtotal = Math.round(weight * rate);
        
        document.getElementById('subtotal_' + id).innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        const route = document.getElementById('route').value;
        let totalWeight = 0;
        let grandTotal = 0;

        document.querySelectorAll('.cat-checkbox').forEach(cb => {
            if (cb.checked) {
                const id = cb.id.split('_')[1];
                const weight = parseFloat(document.getElementById('weight_' + id).value) || 0;
                totalWeight += weight;

                if (route === 'agent') {
                    const price = parseFloat(document.getElementById('price_' + id).value) || 0;
                    grandTotal += Math.round(weight * price);
                }
            }
        });

        document.getElementById('grand-weight').innerText = totalWeight.toFixed(2).replace('.', ',') + ' kg';
        document.getElementById('grand-total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    // Initialize state
    document.addEventListener('DOMContentLoaded', () => {
        toggleRouteFields(document.getElementById('route').value);
        document.querySelectorAll('.cat-checkbox').forEach(cb => {
            const id = cb.id.split('_')[1];
            toggleCategoryInput(id);
        });
    });

    // Form submit validation
    document.getElementById('dist-form').addEventListener('submit', function(e) {
        let hasSelection = false;
        document.querySelectorAll('.cat-checkbox').forEach(cb => {
            if (cb.checked) hasSelection = true;
        });

        if (!hasSelection) {
            e.preventDefault();
            alert('Silakan pilih minimal satu jenis sampah yang ingin didistribusikan!');
            return false;
        }

        // Validate weight limit
        let exceedStock = false;
        document.querySelectorAll('.cat-checkbox').forEach(cb => {
            if (cb.checked) {
                const id = cb.id.split('_')[1];
                const input = document.getElementById('weight_' + id);
                const weight = parseFloat(input.value) || 0;
                const maxVal = parseFloat(input.getAttribute('max')) || 0;
                
                if (weight > maxVal) {
                    exceedStock = true;
                    input.style.borderColor = 'var(--danger)';
                } else {
                    input.style.borderColor = '';
                }
            }
        });

        if (exceedStock) {
            e.preventDefault();
            alert('Terdapat input timbangan yang melebihi batas stok gudang! Harap sesuaikan timbangan Anda.');
            return false;
        }
    });
</script>
@endsection
