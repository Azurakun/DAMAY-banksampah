@extends('layouts.app')
@section('title', 'Kelola Kategori Sampah — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    {{-- Header --}}
    <div class="greeting-row">
        <div class="greeting-avatar" style="border-color:var(--accent);">
            <i class="bi bi-tag" style="color:white;font-size:22px;"></i>
        </div>
        <div>
            <div class="greeting-name">Kelola Kategori & Harga Sampah</div>
            <div class="greeting-meta">Atur parameter harga beli dan poin reward per kilogram jenis sampah</div>
        </div>
        <div style="margin-left:auto;">
            <a href="{{ route('manajer.categories.create') }}" class="btn btn-primary" style="background:var(--primary); border-color:var(--primary); color:white; font-weight:700; box-shadow:var(--shadow-sm); display:inline-flex; align-items:center; gap:8px;">
                <i class="bi bi-plus-lg"></i> Tambah Kategori
            </a>
        </div>
    </div>

    {{-- Categories List Card --}}
    <div class="card" style="margin-top:var(--s-20);">
        <div class="flex-between" style="margin-bottom:var(--s-16);">
            <h2 style="font-size:15px;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:var(--s-8);">
                <i class="bi bi-tags" style="color:var(--primary);"></i> Daftar Kategori Sampah
            </h2>
            <span class="badge badge-primary">{{ count($categories) }} Kategori</span>
        </div>

        <div class="table-overflow">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align:center;">Ikon</th>
                        <th>Nama Kategori</th>
                        <th>Slug / Key</th>
                        <th style="text-align:right;">Harga Beli (per kg)</th>
                        <th style="text-align:right;">Poin Reward (per kg)</th>
                        <th style="text-align:right;">Stok Gudang saat ini</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td style="text-align:center; font-size:22px; display:flex; align-items:center; justify-content:center;">
                                @if(Str::startsWith($cat->icon, '/uploads/') || Str::startsWith($cat->icon, 'http'))
                                    <img src="{{ $cat->icon }}" alt="{{ $cat->name }}" style="width: 32px; height: 32px; object-fit: cover; border-radius: 6px; box-shadow: var(--shadow-sm);">
                                @else
                                    {{ $cat->icon }}
                                @endif
                            </td>
                            <td style="font-weight:700; color:var(--primary);">{{ $cat->name }}</td>
                            <td style="font-family:var(--font-mono); font-size:13px;">{{ $cat->key }}</td>
                            <td style="text-align:right; font-weight:700; font-family:var(--font-mono);">
                                Rp {{ number_format($cat->price_per_kg, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right; font-weight:700; font-family:var(--font-mono); color:var(--accent);">
                                {{ number_format($cat->points_per_kg, 0, ',', '.') }} Poin
                            </td>
                            <td style="text-align:right; font-weight:700; font-family:var(--font-mono); color:var(--teal);">
                                {{ number_format($cat->available_stock, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align:center; display:flex; gap:6px; justify-content:center;">
                                <a href="{{ route('manajer.categories.edit', $cat->id) }}" class="btn btn-outline" style="padding: 4px 8px; font-size: 11.5px; font-weight: 700; border: 1.5px solid var(--outline); background: transparent; color: var(--primary); text-decoration: none; border-radius:var(--r-sm); display: inline-flex; align-items: center; gap: 2px;">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                
                                <form action="{{ route('manajer.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori &quot;{{ $cat->name }}&quot;? Kategori yang sudah memiliki transaksi tidak dapat dihapus.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" style="padding: 4px 8px; font-size: 11.5px; font-weight: 700; border: 1.5px solid var(--danger); background: transparent; color: var(--danger); border-radius:var(--r-sm); display: inline-flex; align-items: center; gap: 2px; cursor:pointer;">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:var(--s-32); color:var(--on-surface-variant);">
                                <i class="bi bi-info-circle" style="font-size:24px; display:block; margin-bottom:8px; color:var(--outline);"></i>
                                Belum ada kategori sampah terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
