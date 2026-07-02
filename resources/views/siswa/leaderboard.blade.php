@extends('layouts.app')
@section('title', 'Papan Peringkat — EcoBank SMKN 2 Indramayu')

@section('content')
<div class="animate-fade-in">

    <a href="{{ route('siswa.dashboard') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Beranda
    </a>

    {{-- Leaderboard Hero --}}
    <div class="balance-card" style="background:linear-gradient(135deg, hsl(38,96%,38%) 0%, hsl(30,90%,28%) 100%);margin-bottom:var(--s-20);">
        <div class="balance-card-inner">
            <div style="display:flex;align-items:center;gap:var(--s-12);">
                <div style="font-size:48px;line-height:1;filter:drop-shadow(0 4px 8px rgba(0,0,0,0.25));">🏆</div>
                <div>
                    <div class="balance-label">Papan Peringkat</div>
                    <div style="font-size:20px;font-weight:800;line-height:1.2;margin-bottom:var(--s-6);">Eco-Hero Daur Ulang</div>
                    <div class="points-badge" style="background:rgba(255,255,255,0.2);border-color:rgba(255,255,255,0.3);color:white;font-size:12px;">
                        <i class="bi bi-person"></i> Peringkat Anda: #{{ $myRank }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 3 Podium --}}
    @if($students->count() >= 3)
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--s-8);margin-bottom:var(--s-20);align-items:flex-end;">

        {{-- 2nd Place --}}
        <div class="card text-center" style="border-color:hsl(0,0%,75%);padding:var(--s-12) var(--s-8);margin-bottom:0;">
            <div style="font-size:32px;margin-bottom:var(--s-4);">🥈</div>
            <div style="width:44px;height:44px;border-radius:var(--r-full);background:hsl(0,0%,78%);display:flex;align-items:center;justify-content:center;margin:0 auto var(--s-6);font-weight:800;font-size:18px;color:hsl(0,0%,25%);">2</div>
            <div style="font-size:12px;font-weight:800;line-height:1.2;margin-bottom:2px;">{{ explode(' ', $students[1]->name)[0] }}</div>
            <div style="font-size:10px;color:var(--on-surface-variant);">{{ $students[1]->class }}</div>
            <div style="font-size:13px;font-weight:800;color:var(--primary);margin-top:var(--s-4);">{{ number_format($students[1]->points, 0, ',', '.') }} <span style="font-size:10px;font-weight:600;">poin</span></div>
        </div>

        {{-- 1st Place --}}
        <div class="card text-center" style="border-color:hsl(47,100%,55%);padding:var(--s-16) var(--s-8);margin-bottom:0;background:linear-gradient(135deg,hsl(47,100%,97%) 0%,hsl(38,96%,93%) 100%);">
            <div style="font-size:40px;margin-bottom:var(--s-4);">👑</div>
            <div style="width:52px;height:52px;border-radius:var(--r-full);background:hsl(47,100%,50%);display:flex;align-items:center;justify-content:center;margin:0 auto var(--s-8);font-weight:800;font-size:22px;color:hsl(35,80%,20%);box-shadow:0 4px 12px hsla(38,96%,42%,0.35);">1</div>
            <div style="font-size:13px;font-weight:800;line-height:1.2;margin-bottom:2px;">{{ explode(' ', $students[0]->name)[0] }}</div>
            <div style="font-size:10px;color:var(--on-surface-variant);">{{ $students[0]->class }}</div>
            <div style="font-size:15px;font-weight:800;color:var(--accent);margin-top:var(--s-6);">{{ number_format($students[0]->points, 0, ',', '.') }} <span style="font-size:10px;font-weight:600;">poin</span></div>
        </div>

        {{-- 3rd Place --}}
        <div class="card text-center" style="border-color:hsl(27,65%,55%);padding:var(--s-12) var(--s-8);margin-bottom:0;">
            <div style="font-size:32px;margin-bottom:var(--s-4);">🥉</div>
            <div style="width:44px;height:44px;border-radius:var(--r-full);background:hsl(27,65%,50%);display:flex;align-items:center;justify-content:center;margin:0 auto var(--s-6);font-weight:800;font-size:18px;color:white;">3</div>
            <div style="font-size:12px;font-weight:800;line-height:1.2;margin-bottom:2px;">{{ explode(' ', $students[2]->name)[0] }}</div>
            <div style="font-size:10px;color:var(--on-surface-variant);">{{ $students[2]->class }}</div>
            <div style="font-size:13px;font-weight:800;color:var(--primary);margin-top:var(--s-4);">{{ number_format($students[2]->points, 0, ',', '.') }} <span style="font-size:10px;font-weight:600;">poin</span></div>
        </div>
    </div>
    @endif

    {{-- Full List --}}
    <div class="section-row">
        <span class="section-title">Semua Peringkat</span>
        <span class="badge badge-primary">{{ $students->count() }} Siswa</span>
    </div>

    <div class="leaderboard-list">
        @foreach($students as $index => $student)
            @php
                $rank = $index + 1;
                $isMe = $student->id === Auth::user()->id;
            @endphp
            <div class="leaderboard-item {{ $isMe ? 'my-rank' : '' }} {{ $rank === 1 ? 'rank-1-item' : '' }}">
                <div class="leaderboard-student">
                    <span class="rank-badge {{ $rank <= 3 ? 'rank-'.$rank : '' }}">{{ $rank }}</span>
                    <div>
                        <div class="student-name">
                            {{ $student->name }}
                            @if($isMe)
                                <span class="badge badge-primary" style="margin-left:4px;font-size:9px;padding:1px 6px;">Anda</span>
                            @endif
                        </div>
                        <div class="student-class">Kelas: {{ $student->class }}</div>
                    </div>
                </div>
                <div class="leaderboard-points">
                    <span><i class="bi bi-star" style="font-size:12px;color:var(--accent);margin-right:3px;"></i>{{ number_format($student->points, 0, ',', '.') }}</span>
                    <span style="display:block;font-size:9.5px;font-weight:600;color:var(--on-surface-variant);text-transform:uppercase;">Poin</span>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
