{{--
    Shared Department Race Board partial
    Variables expected:
        $boardTitle     - e.g. "Đường Đua Tư Vấn"
        $boardSubtitle  - e.g. "Chiến Binh Tư Vấn"
        $colLeftTitle   - e.g. "🏆 Xếp Hạng Hoàn Thành"
        $colRightTitle  - e.g. "📊 Xếp Hạng Tiến Độ"
        $completionRankings  - collection: name, avatar_url, finished, total
        $rateRankings        - collection: name, avatar_url, finished, total, pct
        $years
        $year
        $wireYearModel  - wire model string e.g. "year"
--}}
<div class="dept-race-board" x-data>
    @php
        function deptInitials($name) {
            $parts = explode(' ', trim($name));
            $last = array_slice($parts, -2);
            return strtoupper(implode('', array_map(fn($w) => mb_substr($w, 0, 1), $last)));
        }
    @endphp

    {{-- NEBULA BLOBS --}}
    <div class="dept-nebula dept-nebula-1"></div>
    <div class="dept-nebula dept-nebula-2"></div>
    <div class="dept-nebula dept-nebula-3"></div>

    {{-- STAR CANVAS --}}
    <canvas class="dept-stars-canvas" id="deptStars" wire:ignore></canvas>

    <div class="dept-wrapper">

        {{-- FILTERS --}}
        <div class="dept-filters">
            <select wire:model.live="{{ $wireYearModel ?? 'year' }}" class="dept-select">
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        {{-- HEADER --}}
        <div class="dept-header">
            <div class="dept-header-badge">{{ $boardTitle }} – {{ $boardSubtitle }} – {{ $year }}</div>
            <div class="dept-header-sub">"Mỗi hợp đồng hoàn thành là một dấu ấn tự hào"</div>
        </div>
        <div class="dept-gold-divider"></div>

        {{-- COLUMNS --}}
        <div class="dept-columns">

            {{-- LEFT: Completion Rate --}}
            <div class="dept-section">
                <div class="dept-col-title">{{ $colLeftTitle }}</div>

                @if($rateRankings->isEmpty())
                    <div class="dept-empty">Không có dữ liệu</div>
                @else
                    @if($rateRankings->count() >= 3)
                    <div class="dept-podium">
                        {{-- #2 --}}
                        <div class="dept-podium-slot dept-podium-2">
                            @php $p = $rateRankings[1]; @endphp
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-md dept-border-silver">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ deptInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-silver">2</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-2">
                                <div class="dept-podium-name">{{ $p['name'] }}</div>
                                <div class="dept-podium-value">{{ $p['pct'] }}%</div>
                                <div class="dept-podium-sub">{{ $p['finished'] }}/{{ $p['total'] }} HĐ</div>
                            </div>
                        </div>

                        {{-- #1 --}}
                        <div class="dept-podium-slot dept-podium-1">
                            @php $p = $rateRankings[0]; @endphp
                            <div class="dept-crown">👑</div>
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-lg dept-border-gold">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ deptInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-gold">1</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-1">
                                <div class="dept-podium-name dept-name-gold">{{ $p['name'] }}</div>
                                <div class="dept-podium-value dept-value-gold">{{ $p['pct'] }}%</div>
                                <div class="dept-podium-sub">{{ $p['finished'] }}/{{ $p['total'] }} HĐ</div>
                            </div>
                        </div>

                        {{-- #3 --}}
                        <div class="dept-podium-slot dept-podium-3">
                            @php $p = $rateRankings[2]; @endphp
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-md dept-border-bronze">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ deptInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-bronze">3</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-3">
                                <div class="dept-podium-name">{{ $p['name'] }}</div>
                                <div class="dept-podium-value">{{ $p['pct'] }}%</div>
                                <div class="dept-podium-sub">{{ $p['finished'] }}/{{ $p['total'] }} HĐ</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach($rateRankings->values() as $i => $row)
                        @if($rateRankings->count() >= 3 && $i < 3) @continue @endif
                        @php $rank = $i + 1; @endphp
                        <div class="dept-rank-card">
                            <div class="dept-rank-num">{{ $rank }}.</div>
                            <div class="dept-avatar dept-avatar-sm">
                                @if($row['avatar_url'])
                                    <img src="{{ $row['avatar_url'] }}" alt="{{ $row['name'] }}">
                                @else
                                    {{ deptInitials($row['name']) }}
                                @endif
                            </div>
                            <div class="dept-card-name">{{ $row['name'] }}</div>
                            <div class="dept-card-value">{{ $row['pct'] }}%<span class="dept-card-sub"> ({{ $row['finished'] }}/{{ $row['total'] }})</span></div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="dept-col-divider"></div>

            {{-- RIGHT: Completion Count --}}
            <div class="dept-section">
                <div class="dept-col-title">{{ $colRightTitle }}</div>

                @if($completionRankings->isEmpty())
                    <div class="dept-empty">Không có dữ liệu</div>
                @else
                    @if($completionRankings->count() >= 3)
                    <div class="dept-podium">
                        {{-- #2 --}}
                        <div class="dept-podium-slot dept-podium-2">
                            @php $p = $completionRankings[1]; @endphp
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-md dept-border-silver">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ deptInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-silver">2</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-2">
                                <div class="dept-podium-name">{{ $p['name'] }}</div>
                                <div class="dept-podium-value">{{ $p['finished'] }} HĐ</div>
                                <div class="dept-podium-sub">/ {{ $p['total'] }} tổng</div>
                            </div>
                        </div>

                        {{-- #1 --}}
                        <div class="dept-podium-slot dept-podium-1">
                            @php $p = $completionRankings[0]; @endphp
                            <div class="dept-crown">👑</div>
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-lg dept-border-gold">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ deptInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-gold">1</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-1">
                                <div class="dept-podium-name dept-name-gold">{{ $p['name'] }}</div>
                                <div class="dept-podium-value dept-value-gold">{{ $p['finished'] }} HĐ</div>
                                <div class="dept-podium-sub">/ {{ $p['total'] }} tổng</div>
                            </div>
                        </div>

                        {{-- #3 --}}
                        <div class="dept-podium-slot dept-podium-3">
                            @php $p = $completionRankings[2]; @endphp
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-md dept-border-bronze">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ deptInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-bronze">3</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-3">
                                <div class="dept-podium-name">{{ $p['name'] }}</div>
                                <div class="dept-podium-value">{{ $p['finished'] }} HĐ</div>
                                <div class="dept-podium-sub">/ {{ $p['total'] }} tổng</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach($completionRankings->values() as $i => $row)
                        @if($completionRankings->count() >= 3 && $i < 3) @continue @endif
                        @php $rank = $i + 1; @endphp
                        <div class="dept-rank-card">
                            <div class="dept-rank-num">{{ $rank }}.</div>
                            <div class="dept-avatar dept-avatar-sm">
                                @if($row['avatar_url'])
                                    <img src="{{ $row['avatar_url'] }}" alt="{{ $row['name'] }}">
                                @else
                                    {{ deptInitials($row['name']) }}
                                @endif
                            </div>
                            <div class="dept-card-name">{{ $row['name'] }}</div>
                            <div class="dept-card-value">{{ $row['finished'] }} HĐ<span class="dept-card-sub"> /{{ $row['total'] }} tổng</span></div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="dept-footer-quote">"Chiến binh không sợ khó – chỉ sợ không cố gắng hết mình."</div>
    </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════
   DEPT RACE BOARD — shared department leaderboard styles
   ══════════════════════════════════════════════════════════ */
.dept-race-board {
    --dept-gold: #f5c842;
    --dept-gold-light: #ffe88a;
    --dept-gold-dark: #c9a227;
    --dept-bg: #03111f;
    --dept-text: #f0f4ff;

    position: relative;
    background: var(--dept-bg);
    color: var(--dept-text);
    min-height: 100vh;
    margin: -1.5rem;
    padding: 0;
    overflow: hidden;
    font-family: 'Be Vietnam Pro', 'Segoe UI', sans-serif;
}
.dept-nebula {
    position: absolute; border-radius: 50%;
    filter: blur(80px); opacity: 0.18;
    pointer-events: none; z-index: 0;
}
.dept-nebula-1 { width: 600px; height: 600px; background: #1e6fa8; top: -100px; left: -150px; }
.dept-nebula-2 { width: 400px; height: 400px; background: #8b3cdb; top: 300px; right: -100px; }
.dept-nebula-3 { width: 500px; height: 300px; background: #0d4a7a; bottom: 100px; left: 30%; }
.dept-stars-canvas {
    position: absolute; inset: 0; z-index: 0;
    pointer-events: none; width: 100%; height: 100%;
}
.dept-wrapper {
    position: relative; z-index: 1;
    max-width: 1600px; margin: 0 auto;
    padding: 48px 56px 100px;
}
/* FILTERS */
.dept-filters { display: flex; gap: 10px; margin-bottom: 28px; }
.dept-select {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.2);
    color: var(--dept-text); padding: 10px 18px;
    border-radius: 10px; font-size: 1rem; cursor: pointer; outline: none;
}
.dept-select:focus { border-color: var(--dept-gold); }
.dept-select option { background: #0d1b2a; color: #fff; }
/* HEADER */
.dept-header { text-align: center; margin-bottom: 12px; animation: deptFadeDown .8s ease both; }
.dept-header-badge {
    display: inline-block;
    background: linear-gradient(135deg, #c9a227, #f5c842, #ffe88a, #c9a227);
    background-size: 200% 200%;
    animation: deptShimmer 3s linear infinite;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    font-family: 'Playfair Display', 'Be Vietnam Pro', serif;
    font-size: clamp(1.5rem, 3.5vw, 2.6rem);
    font-weight: 900; letter-spacing: 1.5px; line-height: 1.3; text-transform: uppercase;
}
.dept-header-sub { font-size: 1.05rem; color: rgba(255,255,255,0.45); font-style: italic; margin-top: 8px; }
/* DIVIDER */
.dept-gold-divider {
    width: 280px; height: 3px;
    background: linear-gradient(90deg, transparent, var(--dept-gold), transparent);
    margin: 16px auto 44px;
}
/* COLUMNS */
.dept-columns { display: grid; grid-template-columns: 1fr 2px 1fr; gap: 0; }
.dept-col-divider {
    background: linear-gradient(180deg, transparent, var(--dept-gold-dark), var(--dept-gold), var(--dept-gold-dark), transparent);
    opacity: 0.5; width: 2px; justify-self: center;
}
.dept-section { padding: 0 28px; }
/* COL TITLE */
.dept-col-title {
    text-align: center;
    font-size: clamp(1rem, 1.8vw, 1.35rem);
    font-weight: 800; letter-spacing: 3px; text-transform: uppercase;
    color: var(--dept-gold-light); margin-bottom: 36px;
}
.dept-col-title::after {
    content: ''; display: block; width: 80px; height: 3px;
    background: var(--dept-gold); margin: 10px auto 0; border-radius: 2px;
}
/* PODIUM */
.dept-podium {
    display: flex; align-items: flex-end; justify-content: center;
    gap: 6px; margin-bottom: 40px; padding-top: 30px;
}
.dept-podium-slot {
    display: flex; flex-direction: column; align-items: center;
    position: relative; animation: deptFadeUp .6s ease both; width: 180px;
}
.dept-podium-1 { animation-delay: .1s; }
.dept-podium-2 { animation-delay: .2s; }
.dept-podium-3 { animation-delay: .3s; }
/* AVATAR WRAP */
.dept-avatar-wrap { position: relative; display: inline-block; margin-bottom: -20px; z-index: 2; }
.dept-avatar-wrap .dept-medal { position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); margin: 0; }
/* PEDESTAL */
.dept-pedestal {
    width: 100%; display: flex; flex-direction: column;
    align-items: center; justify-content: flex-start;
    padding-top: 28px; border-radius: 12px 12px 4px 4px;
}
.dept-pedestal-1 {
    height: 150px;
    background: linear-gradient(180deg, #2d5fa8 0%, #1a3d7a 40%, #0f2650 100%);
    box-shadow: 0 4px 30px rgba(30,80,180,.4), inset 0 1px 0 rgba(255,255,255,.15);
}
.dept-pedestal-2 {
    height: 118px;
    background: linear-gradient(180deg, #264d8e 0%, #17336a 40%, #0d2245 100%);
    box-shadow: 0 4px 24px rgba(23,60,140,.3), inset 0 1px 0 rgba(255,255,255,.1);
}
.dept-pedestal-3 {
    height: 95px;
    background: linear-gradient(180deg, #264d8e 0%, #17336a 40%, #0d2245 100%);
    box-shadow: 0 4px 24px rgba(23,60,140,.3), inset 0 1px 0 rgba(255,255,255,.1);
}
.dept-podium-name {
    font-weight: 800; font-size: 1rem; text-align: center;
    max-width: 170px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    text-transform: uppercase; letter-spacing: .5px;
}
.dept-podium-value { font-weight: 700; font-size: 1.1rem; color: var(--dept-gold); margin-top: 4px; }
.dept-podium-sub { font-size: .82rem; color: rgba(255,255,255,0.4); margin-top: 2px; }
.dept-name-gold { color: var(--dept-gold-light); font-size: 1.1rem; }
.dept-value-gold { color: var(--dept-gold); font-size: 1.18rem; }
/* CROWN */
.dept-crown {
    font-size: 1.8rem; margin-bottom: 2px;
    animation: deptFloat 2.5s ease-in-out infinite;
    filter: drop-shadow(0 2px 6px rgba(245,200,66,.7));
}
/* AVATAR */
.dept-avatar {
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: var(--dept-gold-light);
    background: linear-gradient(135deg, #1e3c5a, #0d2035);
    overflow: hidden; flex-shrink: 0;
}
.dept-avatar img { width: 100%; height: 100%; object-fit: cover; }
.dept-avatar-lg { width: 130px; height: 130px; font-size: 2.2rem; }
.dept-avatar-md { width: 100px; height: 100px; font-size: 1.6rem; }
.dept-avatar-sm { width: 48px; height: 48px; font-size: 1rem; }
.dept-border-gold   { border: 4px solid var(--dept-gold); box-shadow: 0 0 24px rgba(245,200,66,.5); }
.dept-border-silver { border: 3px solid #b8b8b8; box-shadow: 0 0 16px rgba(200,200,200,.3); }
.dept-border-bronze { border: 3px solid #c06a2a; box-shadow: 0 0 16px rgba(192,106,42,.3); }
/* MEDAL */
.dept-medal {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 1rem; z-index: 3;
}
.dept-medal-gold {
    background: radial-gradient(circle at 35% 35%, #ffe88a, #c9a227);
    color: #6b4400; box-shadow: 0 0 14px rgba(245,200,66,.6);
    animation: deptPulseGlow 2.5s ease-in-out infinite;
}
.dept-medal-silver { background: radial-gradient(circle at 35% 35%, #e8e8e8, #9e9e9e); color: #333; box-shadow: 0 0 10px rgba(200,200,200,.3); }
.dept-medal-bronze { background: radial-gradient(circle at 35% 35%, #f8b87a, #c06a2a); color: #5a1a00; box-shadow: 0 0 10px rgba(192,106,42,.3); }
/* RANK CARDS (4+) */
.dept-rank-card {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 22px; border-radius: 12px; margin-bottom: 10px;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);
    transition: transform .25s, border-color .25s, background .25s;
    animation: deptFadeUp .5s ease both;
}
.dept-rank-card:hover {
    transform: translateX(4px);
    border-color: rgba(245,200,66,0.2);
    background: rgba(255,255,255,0.07);
}
.dept-rank-num { font-weight: 800; font-size: 1.1rem; color: rgba(255,255,255,0.45); flex-shrink: 0; width: 32px; text-align: center; }
.dept-card-name { flex: 1; font-weight: 700; font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-transform: uppercase; letter-spacing: .3px; }
.dept-card-value { font-weight: 700; font-size: 1.05rem; color: var(--dept-gold); flex-shrink: 0; text-align: right; }
.dept-card-sub { font-weight: 400; font-size: .88rem; color: rgba(255,255,255,0.4); }
.dept-empty { text-align: center; color: rgba(255,255,255,0.3); padding: 40px 0; font-style: italic; }
.dept-footer-quote { text-align: center; margin-top: 56px; font-size: 1rem; color: rgba(255,255,255,0.25); font-style: italic; }
/* ANIMATIONS */
@keyframes deptFadeDown { from { opacity: 0; transform: translateY(-24px); } to { opacity: 1; transform: translateY(0); } }
@keyframes deptFadeUp   { from { opacity: 0; transform: translateY(20px);  } to { opacity: 1; transform: translateY(0); } }
@keyframes deptShimmer  { 0% { background-position: 0% 50%; } 100% { background-position: 200% 50%; } }
@keyframes deptFloat    { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
@keyframes deptPulseGlow {
    0%, 100% { box-shadow: 0 0 14px rgba(245,200,66,.6); }
    50%      { box-shadow: 0 0 24px rgba(245,200,66,.9), 0 0 40px rgba(245,200,66,.3); }
}
/* RESPONSIVE */
@media (max-width: 900px) {
    .dept-columns { grid-template-columns: 1fr; gap: 40px 0; }
    .dept-col-divider { display: none; }
    .dept-section { padding: 0; }
    .dept-podium { gap: 4px; }
    .dept-podium-slot { width: 140px; }
    .dept-avatar-lg { width: 90px; height: 90px; font-size: 1.6rem; }
    .dept-avatar-md { width: 70px; height: 70px; font-size: 1.2rem; }
    .dept-pedestal-1 { height: 118px; }
    .dept-pedestal-2 { height: 92px; }
    .dept-pedestal-3 { height: 75px; }
    .dept-race-board { margin: -1rem; }
    .dept-wrapper { padding: 20px 16px 60px; }
}
</style>

@push('scripts')
<script>
(function(){
    const canvas = document.getElementById('deptStars');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let stars = [];
    function resize() {
        const board = canvas.closest('.dept-race-board');
        if (!board) return;
        canvas.width  = board.offsetWidth;
        canvas.height = board.offsetHeight;
    }
    function init() {
        resize(); stars = [];
        for (let i = 0; i < 200; i++) {
            stars.push({ x: Math.random() * canvas.width, y: Math.random() * canvas.height,
                r: Math.random() * 1.2 + 0.2, a: Math.random(), da: (Math.random() - 0.5) * 0.008 });
        }
    }
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        stars.forEach(s => {
            s.a += s.da;
            if (s.a <= 0 || s.a >= 1) s.da *= -1;
            ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255,255,255,${s.a * 0.85})`; ctx.fill();
        });
        requestAnimationFrame(draw);
    }
    init(); draw();
    window.addEventListener('resize', resize);
    document.addEventListener('livewire:navigated', () => { init(); draw(); });
})();
</script>
@endpush
