@extends('layouts.app')
@section('title', 'Profil Operator — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in" style="max-width:520px;margin:0 auto;">

    <a href="{{ route('operator.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Dasbor
    </a>

    <div class="card" style="padding:0;overflow:hidden;">

        {{-- Profile Hero --}}
        <div class="profile-hero" style="background:linear-gradient(180deg,var(--primary-container) 0%,var(--surface) 100%);">
            <div class="profile-avatar-circle" style="margin:0 auto var(--s-12);">
                <i class="bi bi-person-badge" style="color:white;font-size:46px;"></i>
            </div>
            <div class="profile-name">{{ $operator->name }}</div>
            <div class="profile-role">
                <span class="badge badge-primary">Operator Bank Sampah</span>
            </div>
        </div>

        <div style="padding:var(--s-20);">

            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-envelope" style="margin-right:4px;"></i>Email</span>
                    <span class="info-value">{{ $operator->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-telephone" style="margin-right:4px;"></i>Telepon</span>
                    <span class="info-value">{{ $operator->phone ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-shield-check" style="margin-right:4px;"></i>Wewenang</span>
                    <span class="info-value" style="font-size:12px;">Catat Setoran &amp; Konfirmasi Tarik</span>
                </div>
            </div>

            <div class="divider-dashed" style="margin-top:var(--s-20);"></div>

            <form action="{{ route('logout') }}" method="POST" style="margin-top:var(--s-12);">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-full">
                    <i class="bi bi-box-arrow-right"></i> Keluar dari Aplikasi
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
