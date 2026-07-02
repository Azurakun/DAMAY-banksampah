@extends('layouts.app')
@section('title', 'Tarik Dana — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="max-width:520px;margin:0 auto;">

    <a href="{{ route('siswa.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Beranda
    </a>

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-icon" style="background:linear-gradient(135deg,var(--accent) 0%,hsl(38,96%,55%) 100%);box-shadow:0 6px 20px hsla(38,96%,42%,0.3);">
            <i class="bi bi-cash-coin"></i>
        </div>
        <div class="page-header-text">
            <h1 class="page-title">Tarik Saldo</h1>
            <p class="page-subtitle">Ajukan penarikan tabungan sampah</p>
        </div>
    </div>

    {{-- Current Balance Display --}}
    <div class="balance-card" style="margin-bottom:var(--s-20);">
        <div class="balance-card-inner">
            <div class="balance-label"><i class="bi bi-wallet2" style="margin-right:5px;"></i>Saldo Tersedia Anda</div>
            <div class="balance-amount">Rp {{ number_format($user->balance, 0, ',', '.') }}</div>
            <div class="balance-footer">
                <div class="points-badge">
                    <i class="bi bi-star" style="font-size:12px;"></i>
                    {{ number_format($user->points, 0, ',', '.') }} Poin
                </div>
                <span class="balance-school">Minimum tarik: Rp 5.000</span>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="font-size:17px;font-weight:800;color:var(--on-surface);margin-bottom:var(--s-20);display:flex;align-items:center;gap:var(--s-8);">
            <i class="bi bi-send" style="color:var(--primary);"></i>
            Formulir Pengajuan Penarikan
        </h2>

        <form action="{{ route('siswa.withdraw.post') }}" method="POST" id="withdraw-form">
            @csrf

            <div class="form-group">
                <label for="amount" class="form-label">
                    Jumlah Penarikan (Rupiah)
                </label>
                <input
                    type="number"
                    id="amount"
                    name="amount"
                    class="form-control"
                    placeholder="Contoh: 50000"
                    min="5000"
                    max="{{ $user->balance }}"
                    required
                    oninput="formatPreview(this.value)"
                >
                {{-- Real-time preview --}}
                <div id="amount-preview" style="display:none;margin-top:var(--s-6);padding:var(--s-8) var(--s-12);background:var(--primary-container);border-radius:var(--r-sm);font-size:13px;font-weight:700;color:var(--primary);">
                    <i class="bi bi-check-circle"></i> <span id="preview-text"></span>
                </div>
                @error('amount')
                    <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="note" class="form-label">Keterangan (Opsional)</label>
                <textarea
                    id="note"
                    name="note"
                    class="form-control"
                    style="height:88px;resize:none;"
                    placeholder="Contoh: Penarikan untuk membeli buku tulis"
                >{{ old('note') }}</textarea>
                @error('note')
                    <span class="form-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>
                @enderror
            </div>

            <div style="background:var(--warning-container);border:1px solid hsl(38,70%,80%);border-radius:var(--r-sm);padding:var(--s-12) var(--s-16);margin-bottom:var(--s-20);display:flex;gap:var(--s-8);font-size:13px;color:var(--accent);">
                <i class="bi bi-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
                <span>Pengajuan akan diproses oleh Operator Bank Sampah. Saldo berkurang setelah disetujui.</span>
            </div>

            <button type="submit" class="btn btn-primary w-full" id="submit-btn">
                <i class="bi bi-send"></i> Ajukan Penarikan
            </button>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function formatPreview(val) {
        const n = parseInt(val) || 0;
        const preview = document.getElementById('amount-preview');
        const text = document.getElementById('preview-text');
        if (n >= 5000) {
            text.textContent = 'Penarikan: Rp ' + n.toLocaleString('id-ID');
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }
</script>
@endsection
