@extends('layouts.app')
@section('title', 'Papan Peringkat Liga — EcoBank SMKN 2 Indramayu')

@section('content')
@php
    $myPoints = Auth::user()->weekly_points;
    $myLeague = Auth::user()->league ?? 'bronze';
    
    // Determine current user league details
    if ($myLeague === 'diamond') {
        $myLeagueName = 'Diamond League (Rank A+)';
        $myLeagueIcon = '💎';
        $myLeagueColor = '#1D7A8C';
        $nextLeagueText = 'Anda berada di liga tertinggi!';
    } elseif ($myLeague === 'gold') {
        $myLeagueName = 'Gold League (Rank A)';
        $myLeagueIcon = '👑';
        $myLeagueColor = '#B8792B';
        $nextLeagueText = 'Berjuanglah untuk naik ke Diamond League!';
    } elseif ($myLeague === 'silver') {
        $myLeagueName = 'Silver League (Rank B)';
        $myLeagueIcon = '🛡️';
        $myLeagueColor = '#7F8C8D';
        $nextLeagueText = 'Berjuanglah untuk naik ke Gold League!';
    } else {
        $myLeague = 'bronze';
        $myLeagueName = 'Bronze League (Rank C)';
        $myLeagueIcon = '🥉';
        $myLeagueColor = '#A77044';
        $nextLeagueText = 'Berjuanglah untuk naik ke Silver League!';
    }

    // Count students per league
    $counts = ['diamond' => 0, 'gold' => 0, 'silver' => 0, 'bronze' => 0];
    foreach($students as $s) {
        $counts[$s->league ?? 'bronze']++;
    }
@endphp

<div class="animate-fade-in" style="margin-bottom:var(--s-32);">

    <a href="{{ route('siswa.dashboard') }}" class="back-link" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; color:var(--primary); text-decoration:none; margin-bottom: 16px;">
        <i class="bi bi-arrow-left"></i> Beranda
    </a>

    {{-- Widescreen Layout --}}
    <div class="dashboard-grid">

        {{-- Left Column: Duolingo League Status & Progress --}}
        <div class="dashboard-column-left">
            
            {{-- Duolingo League Card --}}
            <div class="card" style="border-top: 5px solid {{ $myLeagueColor }}; padding: var(--s-24) var(--s-28); position: relative; overflow: hidden; background: var(--surface);">
                <div style="display:flex; align-items:center; gap:var(--s-20);">
                    <div style="font-size:56px; line-height:1; animation: float 3s ease-in-out infinite; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.15));">{{ $myLeagueIcon }}</div>
                    <div style="flex:1; min-width:0;">
                        <span style="font-size:11px; font-weight:800; text-transform:uppercase; color:{{ $myLeagueColor }}; letter-spacing:1px; display:block; margin-bottom:2px;">Kasta Liga Anda</span>
                        <h3 style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--on-surface); line-height:1.2;">{{ $myLeagueName }}</h3>
                    </div>
                </div>

                <div style="margin-top:var(--s-24);">
                    <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; color:var(--on-surface-variant); margin-bottom:6px; flex-wrap: wrap; gap: 8px;">
                        <span>Poin Turnamen: <strong>{{ number_format($myPoints, 0, ',', '.') }}</strong></span>
                        <span style="color:var(--accent);" id="leaderboard-countdown" data-endtime="{{ \Carbon\Carbon::now()->endOfWeek()->timestamp }}"><i class="bi bi-clock"></i> --</span>
                    </div>
                    <div style="font-size:12.5px; color:var(--on-surface-variant); line-height: 1.45; border-top: 1px dashed var(--outline-variant); padding-top: 10px; margin-top: 8px; margin-bottom: 16px;">
                        {{ $nextLeagueText }} Peringkat Anda ditentukan oleh keaktifan menyetor sampah dibandingkan siswa lain di liga yang sama.
                    </div>
                    <button type="button" class="btn btn-outline" onclick="openRulesModal()" style="height: 38px; width: 100%; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; gap: 6px; border: 1.5px solid var(--outline-variant); color: var(--primary); background: transparent; cursor: pointer; border-radius: var(--r-sm);">
                        <i class="bi bi-info-circle"></i> Info Aturan Liga
                    </button>
                </div>
            </div>

            {{-- Histori Liga Card --}}
            @if(Auth::user()->last_weekly_status)
                @php
                    $lastStatus = Auth::user()->last_weekly_status;
                    $lastRank = Auth::user()->last_weekly_rank;
                    $lastPoints = Auth::user()->last_weekly_points;
                    
                    $statusTitles = [
                        'promoted' => 'Promosi',
                        'demoted' => 'Demosi',
                        'stayed' => 'Bertahan'
                    ];
                    $statusColors = [
                        'promoted' => 'var(--success, #2e7d32)',
                        'demoted' => 'var(--danger, #c62828)',
                        'stayed' => 'var(--on-surface-variant, #666)'
                    ];
                    $statusIcons = [
                        'promoted' => '🎉',
                        'demoted' => '⚠️',
                        'stayed' => '👍'
                    ];
                @endphp
                <div class="card" style="padding: var(--s-20) var(--s-24); border-left: 5px solid {{ $statusColors[$lastStatus] ?? 'var(--outline-variant)' }}; background: var(--surface);">
                    <h4 style="font-size: 13px; font-weight: 800; text-transform: uppercase; color: var(--primary); margin-bottom: var(--s-16); letter-spacing: 0.5px;">
                        <i class="bi bi-clock-history"></i> Hasil Turnamen Lalu
                    </h4>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <div style="font-size:36px; line-height:1;">{{ $statusIcons[$lastStatus] ?? '📅' }}</div>
                        <div>
                            <span style="font-size:11px; font-weight:800; text-transform:uppercase; color:{{ $statusColors[$lastStatus] ?? '#666' }}; letter-spacing:0.5px; display:block; margin-bottom:2px;">Hasil: {{ $statusTitles[$lastStatus] ?? 'Selesai' }}</span>
                            <div style="font-size:14px; font-weight:700; color:var(--on-surface);">Peringkat #{{ $lastRank }} ({{ number_format($lastPoints, 0, ',', '.') }} XP)</div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Right Column: Duolingo League Leaderboards --}}
        <div class="dashboard-column-right">
            
            {{-- Tabs --}}
            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:6px; margin-bottom:var(--s-16); background:var(--surface-container); padding:6px; border-radius:var(--r-md); border: 1px solid var(--outline-variant);">
                <button type="button" class="league-tab {{ $myLeague === 'bronze' ? 'active' : '' }}" onclick="switchLeague('bronze', this)" style="border:none; border-radius:var(--r-sm); padding: 8px 4px; font-weight:800; font-size:11.5px; text-align:center; background:none; cursor:pointer; color:var(--on-surface-variant);">
                    <span style="display:block; font-size:18px; margin-bottom:2px;">🥉</span>
                    Bronze ({{ $counts['bronze'] }})
                </button>
                <button type="button" class="league-tab {{ $myLeague === 'silver' ? 'active' : '' }}" onclick="switchLeague('silver', this)" style="border:none; border-radius:var(--r-sm); padding: 8px 4px; font-weight:800; font-size:11.5px; text-align:center; background:none; cursor:pointer; color:var(--on-surface-variant);">
                    <span style="display:block; font-size:18px; margin-bottom:2px;">🛡️</span>
                    Silver ({{ $counts['silver'] }})
                </button>
                <button type="button" class="league-tab {{ $myLeague === 'gold' ? 'active' : '' }}" onclick="switchLeague('gold', this)" style="border:none; border-radius:var(--r-sm); padding: 8px 4px; font-weight:800; font-size:11.5px; text-align:center; background:none; cursor:pointer; color:var(--on-surface-variant);">
                    <span style="display:block; font-size:18px; margin-bottom:2px;">👑</span>
                    Gold ({{ $counts['gold'] }})
                </button>
                <button type="button" class="league-tab {{ $myLeague === 'diamond' ? 'active' : '' }}" onclick="switchLeague('diamond', this)" style="border:none; border-radius:var(--r-sm); padding: 8px 4px; font-weight:800; font-size:11.5px; text-align:center; background:none; cursor:pointer; color:var(--on-surface-variant);">
                    <span style="display:block; font-size:18px; margin-bottom:2px;">💎</span>
                    Diamond ({{ $counts['diamond'] }})
                </button>
            </div>

            {{-- Leaderboard list --}}
            <div class="card" style="padding:var(--s-24) var(--s-20);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--s-16);">
                    <h3 style="font-family:var(--font-display); font-size:16px; font-weight:800; color:var(--primary); display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-trophy" style="color:var(--accent);"></i> Klasemen Papan Peringkat
                    </h3>
                    <span class="badge badge-teal" id="league-total-badge" style="font-weight:700;">— Siswa</span>
                </div>

                <div class="leaderboard-list" style="position: relative;">
                    @foreach($students as $index => $student)
                        @php
                            $isMe = $student->id === Auth::user()->id;
                            $sLeague = $student->league ?? 'bronze';
                        @endphp
                        
                        <div class="leaderboard-item {{ $isMe ? 'my-rank' : '' }} student-row" data-league="{{ $sLeague }}" style="display:none; align-items: center; justify-content: space-between; border-radius: var(--r-md); padding: var(--s-12) var(--s-16); border: 1px solid var(--outline-variant); margin-bottom: 2px;">
                            <div class="leaderboard-student" style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                                <span class="rank-badge-circle rank-badge-other" style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">1</span>
                                
                                {{-- Avatar circle --}}
                                <div class="student-avatar-circle" style="width: 38px; height: 38px; border-radius: 50%; overflow: hidden; background: var(--primary-container, #e8f5e9); color: var(--primary, #123526); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; border: 1.5px solid var(--outline-variant); flex-shrink: 0;">
                                    @if($student->avatar)
                                        <img src="{{ $student->avatar }}" alt="{{ $student->name }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    @endif
                                </div>
                                
                                <div style="min-width: 0; flex: 1;">
                                    <div class="student-name" style="font-weight: 700; font-size: 14px; color: var(--on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 6px;">
                                        {{ $student->name }}
                                        @if($isMe)
                                            <span class="badge badge-primary" style="font-size: 9px; padding: 1px 6px; background: var(--primary); color: white; border-radius: 4px;">Anda</span>
                                        @endif
                                    </div>
                                    <div class="student-class" style="font-size: 11.5px; color: var(--on-surface-variant); font-weight: 500;">Kelas: {{ $student->class }}</div>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
                                <span class="zone-badge badge" style="font-size: 9px; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: var(--r-sm); display: inline-block;">Bertahan</span>
                                <div class="leaderboard-points" style="text-align: right; min-width: 60px;">
                                    <div style="font-family: var(--font-mono); font-weight: 800; font-size: 14px; color: var(--primary);">
                                        <i class="bi bi-lightning-fill" style="color: #FFD700; margin-right: 2px;"></i>{{ number_format($student->weekly_points, 0, ',', '.') }}
                                    </div>
                                    <span style="display: block; font-size: 9px; font-weight: 700; color: var(--on-surface-variant); text-transform: uppercase; letter-spacing: 0.5px;">XP</span>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Empty state when league has no students --}}
                    <div id="league-empty-state" class="empty-state" style="display:none; text-align:center; padding:var(--s-32) var(--s-16);">
                        <i class="bi bi-emoji-neutral" style="font-size:36px; color:var(--outline); display:block; margin-bottom:8px;"></i>
                        <h4 style="font-size:14px; font-weight:700; color:var(--on-surface-variant);">Tidak Ada Siswa</h4>
                        <p style="font-size:12px; color:var(--outline);">Belum ada siswa yang berada di kasta liga ini.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal Aturan Liga -->
    <div id="rulesModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center; opacity:0; transition:opacity 0.25s ease;">
        <div class="card" style="background:var(--surface); max-width:480px; width:90%; border-radius:var(--r-lg); border:1px solid var(--outline-variant); box-shadow:var(--shadow-xl); padding:var(--s-24); position:relative; margin:auto; transform:translateY(-30px); transition:transform 0.25s ease; border-top: 5px solid var(--primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--s-20); border-bottom:1px solid var(--outline-variant); padding-bottom:12px;">
                <h3 style="font-family:var(--font-display); font-size:16px; font-weight:800; color:var(--primary); margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-info-circle-fill" style="color:var(--primary);"></i> Aturan Kenaikan & Penurunan Liga
                </h3>
                <button onclick="closeRulesModal()" style="background:none; border:none; font-size:24px; color:var(--on-surface-variant); cursor:pointer; line-height:1; padding:0;">&times;</button>
            </div>
            
            <div style="display:flex; flex-direction:column; gap:var(--s-16); font-size:13px; color:var(--on-surface-variant); line-height:1.5;">
                <div style="display:flex; align-items:flex-start; gap:12px;">
                    <span style="font-size:20px; line-height:1;">📈</span>
                    <div>
                        <strong style="color:var(--success, #2e7d32); display:block; margin-bottom:2px;">Zona Promosi (Top 20%)</strong>
                        Siswa di peringkat teratas (minimal memiliki 1 XP) akan naik ke kasta liga berikutnya pada hari Minggu pukul 23:59.
                    </div>
                </div>
                <div style="display:flex; align-items:flex-start; gap:12px;">
                    <span style="font-size:20px; line-height:1;">📉</span>
                    <div>
                        <strong style="color:var(--danger, #c62828); display:block; margin-bottom:2px;">Zona Demosi (Bottom 20%)</strong>
                        Siswa di peringkat terbawah akan turun ke kasta liga sebelumnya (tidak berlaku untuk Bronze League).
                    </div>
                </div>
                <div style="display:flex; align-items:flex-start; gap:12px;">
                    <span style="font-size:20px; line-height:1;">🔄</span>
                    <div>
                        <strong style="color:var(--on-surface); display:block; margin-bottom:2px;">Zona Bertahan (Sisa 60%)</strong>
                        Siswa di zona tengah akan tetap berada di kasta liga saat ini untuk turnamen minggu depan.
                    </div>
                </div>
                <div style="display:flex; align-items:flex-start; gap:12px; border-top:1px dashed var(--outline-variant); padding-top:14px; margin-top:4px;">
                    <span style="font-size:20px; line-height:1;">⏰</span>
                    <div>
                        <strong style="color:var(--on-surface); display:block; margin-bottom:2px;">Reset Mingguan</strong>
                        Setiap hari Minggu pukul 23:59:59. Seluruh perolehan XP mingguan akan di-reset ke 0 untuk memulai kompetisi baru.
                    </div>
                </div>
            </div>
            
            <div style="margin-top:var(--s-24); text-align:right;">
                <button class="btn btn-primary" onclick="closeRulesModal()" style="height:36px; padding:0 var(--s-16); font-size:13px; border-radius:var(--r-sm); border:none; background:var(--primary); color:white; font-weight:700; cursor:pointer;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    
    .league-tab {
        transition: all var(--dur-base) var(--ease);
    }
    
    .league-tab:hover {
        background: rgba(18, 53, 38, 0.04) !important;
    }
    
    .league-tab.active {
        background: var(--surface) !important;
        box-shadow: var(--shadow-sm);
        border: 1.5px solid var(--outline) !important;
        color: var(--primary) !important;
    }

    /* Zone styles for premium visual indicator */
    .student-row {
        transition: border-left 0.3s ease, background 0.3s ease;
        border-left: 5px solid transparent !important;
    }
    
    .student-row.zone-promotion {
        border-left-color: var(--success, #2e7d32) !important;
        background: rgba(46, 125, 50, 0.04);
    }
    
    .student-row.zone-demotion {
        border-left-color: var(--danger, #c62828) !important;
        background: rgba(198, 40, 40, 0.04);
    }
    
    .student-row.zone-stay {
        border-left-color: var(--outline-variant, #ccc) !important;
    }
</style>
@endsection

@section('scripts')
<script>
    function switchLeague(leagueKey, buttonEl) {
        // Toggle tab button active classes
        document.querySelectorAll('.league-tab').forEach(btn => btn.classList.remove('active'));
        if (buttonEl) {
            buttonEl.classList.add('active');
        } else {
            // Find tab button matching target league if called on page load
            const tabs = document.querySelectorAll('.league-tab');
            const map = {'bronze': 0, 'silver': 1, 'gold': 2, 'diamond': 3};
            if (tabs[map[leagueKey]]) {
                tabs[map[leagueKey]].classList.add('active');
            }
        }

        // Remove any existing zone dividers before drawing new ones
        document.querySelectorAll('.zone-divider').forEach(el => el.remove());

        // Filter student list
        const rows = document.querySelectorAll('.student-row');
        let visibleCount = 0;
        rows.forEach(row => {
            if (row.getAttribute('data-league') === leagueKey) {
                visibleCount++;
            }
        });

        // Determine Promotion & Demotion zone limits
        let promoLimit = Math.ceil(visibleCount * 0.2);
        let demoteLimit = Math.ceil(visibleCount * 0.2);

        // Prevent overlaps in small count
        if (promoLimit + demoteLimit > visibleCount) {
            if (visibleCount === 1) {
                promoLimit = 1;
                demoteLimit = 0;
            } else {
                promoLimit = 1;
                demoteLimit = 1;
            }
        }

        let index = 1;
        rows.forEach(row => {
            if (row.getAttribute('data-league') === leagueKey) {
                row.style.display = 'flex';
                
                // Style rank badge circle like Duolingo (gold, silver, bronze, neutral)
                const rankBadge = row.querySelector('.rank-badge-circle');
                if (rankBadge) {
                    rankBadge.innerText = index;
                    rankBadge.style.background = '';
                    rankBadge.style.color = '';
                    rankBadge.style.border = '';
                    rankBadge.style.boxShadow = '';
                    
                    if (index === 1) {
                        rankBadge.style.background = 'linear-gradient(135deg, #FFD700, #FFA500)';
                        rankBadge.style.color = '#3B2708';
                        rankBadge.style.border = '1.5px solid #FFF';
                        rankBadge.style.boxShadow = '0 2px 4px rgba(0,0,0,0.15)';
                    } else if (index === 2) {
                        rankBadge.style.background = 'linear-gradient(135deg, #E0E0E0, #9E9E9E)';
                        rankBadge.style.color = '#2A2C24';
                        rankBadge.style.border = '1.5px solid #FFF';
                        rankBadge.style.boxShadow = '0 2px 4px rgba(0,0,0,0.15)';
                    } else if (index === 3) {
                        rankBadge.style.background = 'linear-gradient(135deg, #D7A15C, #A0522D)';
                        rankBadge.style.color = 'white';
                        rankBadge.style.border = '1.5px solid #FFF';
                        rankBadge.style.boxShadow = '0 2px 4px rgba(0,0,0,0.15)';
                    } else {
                        rankBadge.style.background = 'var(--surface-container, #f5f5f5)';
                        rankBadge.style.color = 'var(--on-surface-variant, #666)';
                    }
                }
                
                // Reset zone classes
                row.classList.remove('zone-promotion', 'zone-stay', 'zone-demotion');
                
                const zoneBadge = row.querySelector('.zone-badge');
                let currentZone = 'stay';

                if (leagueKey !== 'diamond' && index <= promoLimit) {
                    row.classList.add('zone-promotion');
                    currentZone = 'promotion';
                    if (zoneBadge) {
                        zoneBadge.innerText = 'Promosi';
                        zoneBadge.style.background = 'var(--success-container, #e8f5e9)';
                        zoneBadge.style.color = 'var(--success, #2e7d32)';
                    }
                } else if (leagueKey !== 'bronze' && index > (visibleCount - demoteLimit)) {
                    row.classList.add('zone-demotion');
                    currentZone = 'demotion';
                    if (zoneBadge) {
                        zoneBadge.innerText = 'Demosi';
                        zoneBadge.style.background = 'var(--danger-container, #ffebee)';
                        zoneBadge.style.color = 'var(--danger, #c62828)';
                    }
                } else {
                    row.classList.add('zone-stay');
                    currentZone = 'stay';
                    if (zoneBadge) {
                        zoneBadge.innerText = 'Bertahan';
                        zoneBadge.style.background = 'var(--surface-container, #f5f5f5)';
                        zoneBadge.style.color = 'var(--on-surface-variant, #666)';
                    }
                }

                // Inject Demotion Line above the first demoted row
                if (leagueKey !== 'bronze' && index === (visibleCount - demoteLimit + 1)) {
                    const demoteLine = document.createElement('div');
                    demoteLine.className = 'zone-divider';
                    demoteLine.style.cssText = 'display: flex; align-items: center; justify-content: center; margin: 12px 0; gap: 8px; width: 100%; width: -webkit-fill-available; grid-column: 1 / -1; animation: fadeIn 0.3s ease;';
                    demoteLine.innerHTML = `
                        <div style="flex: 1; height: 1.5px; background: linear-gradient(to right, transparent, var(--danger, #c62828));"></div>
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--danger, #c62828); letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                            <i class="bi bi-chevron-double-down"></i> Batas Zona Demosi
                        </span>
                        <div style="flex: 1; height: 1.5px; background: linear-gradient(to left, transparent, var(--danger, #c62828));"></div>
                    `;
                    row.before(demoteLine);
                }

                // Inject Promotion Line below the last promoted row
                if (leagueKey !== 'diamond' && index === promoLimit) {
                    // Create the element inside a setTimeout to avoid layout conflict or append issues
                    const promoLine = document.createElement('div');
                    promoLine.className = 'zone-divider';
                    promoLine.style.cssText = 'display: flex; align-items: center; justify-content: center; margin: 12px 0; gap: 8px; width: 100%; width: -webkit-fill-available; grid-column: 1 / -1; animation: fadeIn 0.3s ease;';
                    promoLine.innerHTML = `
                        <div style="flex: 1; height: 1.5px; background: linear-gradient(to right, transparent, var(--success, #2e7d32));"></div>
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--success, #2e7d32); letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                            <i class="bi bi-chevron-double-up"></i> Batas Zona Promosi
                        </span>
                        <div style="flex: 1; height: 1.5px; background: linear-gradient(to left, transparent, var(--success, #2e7d32));"></div>
                    `;
                    row.after(promoLine);
                }
                
                index++;
            } else {
                row.style.display = 'none';
            }
        });

        // Toggle empty state
        const emptyState = document.getElementById('league-empty-state');
        if (visibleCount === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }

        // Update badge total count
        document.getElementById('league-total-badge').innerText = visibleCount + ' Siswa';
    }

    // Countdown logic
    document.addEventListener('DOMContentLoaded', () => {
        const countdownEl = document.getElementById('leaderboard-countdown');
        if (countdownEl) {
            const endTime = parseInt(countdownEl.getAttribute('data-endtime')) * 1000;
            function updateCountdown() {
                const now = new Date().getTime();
                const diff = endTime - now;
                if (diff <= 0) {
                    countdownEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Periode Berakhir';
                    return;
                }
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                let text = '<i class="bi bi-clock"></i> ';
                if (days > 0) {
                    text += days + 'h ' + hours + 'j lagi';
                } else if (hours > 0) {
                    text += hours + 'j ' + minutes + 'm lagi';
                } else {
                    text += minutes + 'm lagi';
                }
                countdownEl.innerHTML = text;
            }
            updateCountdown();
            setInterval(updateCountdown, 60000);
        }

        // Default to current user's league
        const userLeague = "{{ $myLeague }}";
        switchLeague(userLeague, null);
    });

    // Modal controls for rules explanation
    function openRulesModal() {
        const modal = document.getElementById('rulesModal');
        if (modal) {
            modal.style.display = 'flex';
            // Force reflow
            modal.offsetHeight;
            modal.style.opacity = '1';
            modal.querySelector('.card').style.transform = 'translateY(0)';
        }
    }

    function closeRulesModal() {
        const modal = document.getElementById('rulesModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('.card').style.transform = 'translateY(-30px)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }
    }

    // Close modal if user clicks outside of the modal container
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('rulesModal');
        if (modal && e.target === modal) {
            closeRulesModal();
        }
    });
</script>
@endsection
