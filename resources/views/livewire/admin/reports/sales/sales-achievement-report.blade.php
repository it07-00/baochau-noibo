<div class="sales-race-board" x-data>
    @php
        $monthLabel = $filter_month ? 'Tháng ' . str_pad($filter_month, 2, '0', STR_PAD_LEFT) : 'Cả năm';
        function raceInitials($name) {
            $parts = explode(' ', trim($name));
            $last = array_slice($parts, -2);
            return strtoupper(implode('', array_map(fn($w) => mb_substr($w, 0, 1), $last)));
        }
    @endphp

    {{-- NEBULA BLOBS --}}
    <div class="race-nebula race-nebula-1"></div>
    <div class="race-nebula race-nebula-2"></div>
    <div class="race-nebula race-nebula-3"></div>

    {{-- STAR CANVAS --}}
    <canvas class="race-stars-canvas" id="raceStars" wire:ignore></canvas>

    <div class="race-wrapper">

        {{-- DAILY REPORT REMINDER --}}
        @php
            $salesHasDailyReport = \App\Models\DailyReport::where('user_id', auth()->id())
                ->whereDate('date', today())
                ->exists();
        @endphp
        @if(!$salesHasDailyReport)
        <div class="race-reminder">
            <div class="race-reminder-icon">📋</div>
            <div class="race-reminder-text">
                <strong>Bạn chưa gửi báo cáo ngày hôm nay</strong>
                <span>Vui lòng gửi báo cáo trước khi kết thúc ngày làm việc.</span>
            </div>
            <a href="{{ route('app.daily-reports.index') }}" class="race-reminder-btn">Gửi báo cáo</a>
        </div>
        @endif

        {{-- FILTERS --}}
        <div class="race-filters">
            <select wire:model.live="filter_month" class="race-select">
                <option value="">Cả năm</option>
                @foreach($months as $m)
                    <option value="{{ $m }}">Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                @endforeach
            </select>
            <select wire:model.live="year" class="race-select">
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        {{-- COMPANY PROGRESS BAR --}}
        <div class="race-company-progress">
            <div class="race-progress-track">
                <div class="race-progress-bar-wrap">
                    <div class="race-progress-fill" style="width: {{ min($companyPct, 100) }}%"></div>
                    <div class="race-progress-label" style="left: {{ min($companyPct, 100) }}%">
                        {{ number_format($companyActual, 0, ',', '.') }}đ ({{ $companyPct }}%)
                    </div>
                </div>
                <div class="race-progress-end">
                    <span>{{ number_format($companyTarget, 0, ',', '.') }}đ</span>
                </div>
            </div>
        </div>

        {{-- HEADER --}}
        <div class="race-header">
            <div class="race-header-badge">
                Đường Đua Doanh Số – Chiến Binh Bảo Châu – {{ $monthLabel }}/{{ $year }}
            </div>
            <div class="race-header-sub">"Mỗi ngày nỗ lực · mỗi con số là một dấu ấn"</div>
        </div>
        <div class="race-gold-divider"></div>

        {{-- COLUMNS --}}
        <div class="race-columns">

            {{-- LEFT: DOANH SỐ --}}
            <div class="race-section">
                <div class="race-col-title">🏆 Đường Đua Doanh Số</div>

                @if($doanhSoRankings->isEmpty())
                    <div class="race-empty">Không có dữ liệu</div>
                @else
                    {{-- TOP 3 PODIUM WITH PEDESTALS --}}
                    @if($doanhSoRankings->count() >= 3)
                    <div class="race-podium">
                        {{-- #2 --}}
                        <div class="race-podium-slot race-podium-2">
                            @php $p = $doanhSoRankings[1]; @endphp
                            <div class="race-avatar-wrap">
                                <div class="race-avatar race-avatar-md race-border-silver">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ raceInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="race-medal race-medal-silver">2</div>
                            </div>
                            <div class="race-pedestal race-pedestal-2">
                                <div class="race-podium-name">{{ $p['name'] }}</div>
                                <div class="race-podium-value">{{ number_format($p['total'], 0, ',', '.') }}đ</div>
                            </div>
                        </div>

                        {{-- #1 --}}
                        <div class="race-podium-slot race-podium-1">
                            @php $p = $doanhSoRankings[0]; @endphp
                            <div class="race-crown">👑</div>
                            <div class="race-avatar-wrap">
                                <div class="race-avatar race-avatar-lg race-border-gold">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ raceInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="race-medal race-medal-gold">1</div>
                            </div>
                            <div class="race-pedestal race-pedestal-1">
                                <div class="race-podium-name race-name-gold">{{ $p['name'] }}</div>
                                <div class="race-podium-value race-value-gold">{{ number_format($p['total'], 0, ',', '.') }}đ</div>
                            </div>
                        </div>

                        {{-- #3 --}}
                        <div class="race-podium-slot race-podium-3">
                            @php $p = $doanhSoRankings[2]; @endphp
                            <div class="race-avatar-wrap">
                                <div class="race-avatar race-avatar-md race-border-bronze">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ raceInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="race-medal race-medal-bronze">3</div>
                            </div>
                            <div class="race-pedestal race-pedestal-3">
                                <div class="race-podium-name">{{ $p['name'] }}</div>
                                <div class="race-podium-value">{{ number_format($p['total'], 0, ',', '.') }}đ</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- REMAINING RANKS --}}
                    @foreach($doanhSoRankings->skip(3)->values() as $i => $row)
                        @php $rank = $i + 4; @endphp
                        <div class="race-rank-card">
                            <div class="race-rank-num">{{ $rank }}.</div>
                            <div class="race-avatar race-avatar-sm">
                                @if($row['avatar_url'])
                                    <img src="{{ $row['avatar_url'] }}" alt="{{ $row['name'] }}">
                                @else
                                    {{ raceInitials($row['name']) }}
                                @endif
                            </div>
                            <div class="race-card-name">{{ $row['name'] }}</div>
                            <div class="race-card-value">{{ number_format($row['total'], 0, ',', '.') }}đ</div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="race-col-divider"></div>

            {{-- RIGHT: KPI --}}
            <div class="race-section">
                <div class="race-col-title">📊 Đường Đua KPI</div>

                @if($kpiRankings->isEmpty())
                    <div class="race-empty">Không có dữ liệu</div>
                @else
                    {{-- TOP 3 PODIUM WITH PEDESTALS --}}
                    @if($kpiRankings->count() >= 3)
                    <div class="race-podium">
                        {{-- #2 --}}
                        <div class="race-podium-slot race-podium-2">
                            @php $p = $kpiRankings[1]; @endphp
                            <div class="race-avatar-wrap">
                                <div class="race-avatar race-avatar-md race-border-silver">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ raceInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="race-medal race-medal-silver">2</div>
                            </div>
                            <div class="race-pedestal race-pedestal-2">
                                <div class="race-podium-name">{{ $p['name'] }}</div>
                                <div class="race-podium-value">{{ $p['pct'] }}%</div>
                            </div>
                        </div>

                        {{-- #1 --}}
                        <div class="race-podium-slot race-podium-1">
                            @php $p = $kpiRankings[0]; @endphp
                            <div class="race-crown">👑</div>
                            <div class="race-avatar-wrap">
                                <div class="race-avatar race-avatar-lg race-border-gold">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ raceInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="race-medal race-medal-gold">1</div>
                            </div>
                            <div class="race-pedestal race-pedestal-1">
                                <div class="race-podium-name race-name-gold">{{ $p['name'] }}</div>
                                <div class="race-podium-value race-value-gold">{{ $p['pct'] }}%</div>
                            </div>
                        </div>

                        {{-- #3 --}}
                        <div class="race-podium-slot race-podium-3">
                            @php $p = $kpiRankings[2]; @endphp
                            <div class="race-avatar-wrap">
                                <div class="race-avatar race-avatar-md race-border-bronze">
                                    @if($p['avatar_url'])
                                        <img src="{{ $p['avatar_url'] }}" alt="{{ $p['name'] }}">
                                    @else
                                        {{ raceInitials($p['name']) }}
                                    @endif
                                </div>
                                <div class="race-medal race-medal-bronze">3</div>
                            </div>
                            <div class="race-pedestal race-pedestal-3">
                                <div class="race-podium-name">{{ $p['name'] }}</div>
                                <div class="race-podium-value">{{ $p['pct'] }}%</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- REMAINING RANKS --}}
                    @foreach($kpiRankings->skip(3)->values() as $i => $row)
                        @php $rank = $i + 4; @endphp
                        <div class="race-rank-card">
                            <div class="race-rank-num">{{ $rank }}.</div>
                            <div class="race-avatar race-avatar-sm">
                                @if($row['avatar_url'])
                                    <img src="{{ $row['avatar_url'] }}" alt="{{ $row['name'] }}">
                                @else
                                    {{ raceInitials($row['name']) }}
                                @endif
                            </div>
                            <div class="race-card-name">{{ $row['name'] }}</div>
                            <div class="race-card-value">{{ $row['pct'] }}%</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="race-footer-quote">"Chiến binh không sợ khó – chỉ sợ không cố gắng hết mình."</div>
    </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════
   SALES RACE BOARD — Scoped styles
   ══════════════════════════════════════════════════════════ */
.sales-race-board {
    --race-gold: #f5c842;
    --race-gold-light: #ffe88a;
    --race-gold-dark: #c9a227;
    --race-red: #e03232;
    --race-bg: #03111f;
    --race-bg-mid: #071a2e;
    --race-text: #f0f4ff;

    position: relative;
    background: var(--race-bg);
    color: var(--race-text);
    min-height: 100vh;
    margin: -1.5rem;
    padding: 0;
    overflow: hidden;
    font-family: 'Be Vietnam Pro', 'Segoe UI', sans-serif;
}

/* ── NEBULA ── */
.race-nebula {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.18;
    pointer-events: none;
    z-index: 0;
}
.race-nebula-1 { width: 600px; height: 600px; background: #1e6fa8; top: -100px; left: -150px; }
.race-nebula-2 { width: 400px; height: 400px; background: #8b3cdb; top: 300px; right: -100px; }
.race-nebula-3 { width: 500px; height: 300px; background: #0d4a7a; bottom: 100px; left: 30%; }

/* ── STARS ── */
.race-stars-canvas {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    width: 100%;
    height: 100%;
}

/* ── WRAPPER ── */
.race-wrapper {
    position: relative;
    z-index: 1;
    max-width: 1600px;
    margin: 0 auto;
    padding: 48px 56px 100px;
}

/* ── FILTERS ── */
.race-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
}
.race-select {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.2);
    color: var(--race-text);
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 1rem;
    cursor: pointer;
    outline: none;
}
.race-select:focus { border-color: var(--race-gold); }
.race-select option { background: #0d1b2a; color: #fff; }

/* ── COMPANY PROGRESS ── */
.race-company-progress {
    margin-bottom: 44px;
}
.race-progress-track {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 1rem;
    padding: 0 8px;
}

.race-progress-bar-wrap {
    flex: 1;
    height: 20px;
    background: rgba(255,255,255,0.08);
    border-radius: 99px;
    position: relative;
    overflow: visible;
    margin-top: 32px;
}
.race-progress-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #1a7fd4, #3eadff, #6ec6ff);
    transition: width 1.5s cubic-bezier(.25,.46,.45,.94);
    box-shadow: 0 0 14px rgba(62,173,255,.5);
    position: relative;
    min-width: 30px;
}
.race-progress-label {
    position: absolute;
    top: -28px;
    transform: translateX(-50%);
    font-size: .92rem;
    font-weight: 700;
    color: var(--race-gold-light);
    white-space: nowrap;
    text-shadow: 0 1px 6px rgba(0,0,0,.6);
}
.race-progress-end {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .9rem;
    color: rgba(255,255,255,0.5);
    white-space: nowrap;
}

/* ── HEADER ── */
.race-header {
    text-align: center;
    margin-bottom: 12px;
    animation: raceFadeDown .8s ease both;
}
.race-header-badge {
    display: inline-block;
    background: linear-gradient(135deg, #c9a227, #f5c842, #ffe88a, #c9a227);
    background-size: 200% 200%;
    animation: raceShimmer 3s linear infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-family: 'Playfair Display', 'Be Vietnam Pro', serif;
    font-size: clamp(1.5rem, 3.5vw, 2.6rem);
    font-weight: 900;
    letter-spacing: 1.5px;
    line-height: 1.3;
    text-transform: uppercase;
}
.race-header-sub {
    font-size: 1.05rem;
    color: rgba(255,255,255,0.45);
    font-style: italic;
    margin-top: 8px;
}

/* ── DIVIDER ── */
.race-gold-divider {
    width: 280px;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--race-gold), transparent);
    margin: 16px auto 44px;
}

/* ── COLUMNS ── */
.race-columns {
    display: grid;
    grid-template-columns: 1fr 2px 1fr;
    gap: 0;
}
.race-col-divider {
    background: linear-gradient(180deg, transparent, var(--race-gold-dark), var(--race-gold), var(--race-gold-dark), transparent);
    opacity: 0.5;
    width: 2px;
    justify-self: center;
}
.race-section { padding: 0 28px; }

/* ── COLUMN TITLE ── */
.race-col-title {
    text-align: center;
    font-size: clamp(1rem, 1.8vw, 1.35rem);
    font-weight: 800;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--race-gold-light);
    margin-bottom: 36px;
}
.race-col-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 3px;
    background: var(--race-gold);
    margin: 10px auto 0;
    border-radius: 2px;
}

/* ── PODIUM ── */
.race-podium {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 6px;
    margin-bottom: 40px;
    padding-top: 30px;
}
.race-podium-slot {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    animation: raceFadeUp .6s ease both;
    width: 180px;
}
.race-podium-1 { animation-delay: .1s; }
.race-podium-2 { animation-delay: .2s; }
.race-podium-3 { animation-delay: .3s; }

/* ── AVATAR WRAPPER (for medal overlay) ── */
.race-avatar-wrap {
    position: relative;
    display: inline-block;
    margin-bottom: -20px;
    z-index: 2;
}
.race-avatar-wrap .race-medal {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    margin: 0;
}

/* ── PEDESTAL BLOCKS ── */
.race-pedestal {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 28px;
    border-radius: 12px 12px 4px 4px;
    position: relative;
}
.race-pedestal-1 {
    height: 140px;
    background: linear-gradient(180deg, #2d5fa8 0%, #1a3d7a 40%, #0f2650 100%);
    box-shadow: 0 4px 30px rgba(30, 80, 180, .4), inset 0 1px 0 rgba(255,255,255,.15);
}
.race-pedestal-2 {
    height: 110px;
    background: linear-gradient(180deg, #264d8e 0%, #17336a 40%, #0d2245 100%);
    box-shadow: 0 4px 24px rgba(23, 60, 140, .3), inset 0 1px 0 rgba(255,255,255,.1);
}
.race-pedestal-3 {
    height: 90px;
    background: linear-gradient(180deg, #264d8e 0%, #17336a 40%, #0d2245 100%);
    box-shadow: 0 4px 24px rgba(23, 60, 140, .3), inset 0 1px 0 rgba(255,255,255,.1);
}

.race-podium-name {
    font-weight: 800;
    font-size: 1rem;
    text-align: center;
    max-width: 170px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.race-podium-value {
    font-weight: 700;
    font-size: 1.05rem;
    color: var(--race-gold);
    margin-top: 4px;
}
.race-name-gold { color: var(--race-gold-light); font-size: 1.1rem; }
.race-value-gold { color: var(--race-gold); font-size: 1.15rem; }

/* ── CROWN ── */
.race-crown {
    font-size: 1.8rem;
    margin-bottom: 2px;
    animation: raceFloat 2.5s ease-in-out infinite;
    filter: drop-shadow(0 2px 6px rgba(245,200,66,.7));
}

/* ── AVATAR ── */
.race-avatar {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: var(--race-gold-light);
    background: linear-gradient(135deg, #1e3c5a, #0d2035);
    overflow: hidden;
    flex-shrink: 0;
}
.race-avatar img { width: 100%; height: 100%; object-fit: cover; }
.race-avatar-lg { width: 130px; height: 130px; font-size: 2.2rem; }
.race-avatar-md { width: 100px; height: 100px; font-size: 1.6rem; }
.race-avatar-sm { width: 48px; height: 48px; font-size: 1rem; }

.race-border-gold  { border: 4px solid var(--race-gold); box-shadow: 0 0 24px rgba(245,200,66,.5); }
.race-border-silver { border: 3px solid #b8b8b8; box-shadow: 0 0 16px rgba(200,200,200,.3); }
.race-border-bronze { border: 3px solid #c06a2a; box-shadow: 0 0 16px rgba(192,106,42,.3); }

/* ── MEDAL ── */
.race-medal {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1rem;
    z-index: 3;
}
.race-medal-gold {
    background: radial-gradient(circle at 35% 35%, #ffe88a, #c9a227);
    color: #6b4400;
    box-shadow: 0 0 14px rgba(245,200,66,.6);
    animation: racePulseGlow 2.5s ease-in-out infinite;
}
.race-medal-silver {
    background: radial-gradient(circle at 35% 35%, #e8e8e8, #9e9e9e);
    color: #333;
    box-shadow: 0 0 10px rgba(200,200,200,.3);
}
.race-medal-bronze {
    background: radial-gradient(circle at 35% 35%, #f8b87a, #c06a2a);
    color: #5a1a00;
    box-shadow: 0 0 10px rgba(192,106,42,.3);
}

/* ── RANK CARD (4+) — Table-style rows ── */
.race-rank-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 22px;
    border-radius: 12px;
    margin-bottom: 10px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
    transition: transform .25s, border-color .25s, background .25s;
    animation: raceFadeUp .5s ease both;
}
.race-rank-card:hover {
    transform: translateX(4px);
    border-color: rgba(245,200,66,0.2);
    background: rgba(255,255,255,0.07);
}
.race-rank-num {
    font-weight: 800;
    font-size: 1.1rem;
    color: rgba(255,255,255,0.45);
    flex-shrink: 0;
    width: 32px;
    text-align: center;
}
.race-card-name {
    flex: 1;
    font-weight: 700;
    font-size: 1.05rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.race-card-value {
    font-weight: 700;
    font-size: 1.05rem;
    color: var(--race-gold);
    flex-shrink: 0;
    text-align: right;
}

/* ── EMPTY ── */
.race-empty {
    text-align: center;
    color: rgba(255,255,255,0.3);
    padding: 40px 0;
    font-style: italic;
}

/* ── REMINDER ── */
.race-reminder {
    display: flex; align-items: center; gap: 16px;
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.35);
    border-left: 4px solid #f59e0b;
    border-radius: 12px; padding: 14px 20px;
    margin-bottom: 28px;
}
.race-reminder-icon { font-size: 1.6rem; flex-shrink: 0; }
.race-reminder-text { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.race-reminder-text strong { color: #ffe88a; font-size: 1rem; }
.race-reminder-text span { color: rgba(255,255,255,0.55); font-size: .9rem; }
.race-reminder-btn {
    flex-shrink: 0;
    background: linear-gradient(135deg, #d97706, #f59e0b);
    color: #1a0a00; font-weight: 700; font-size: .9rem;
    padding: 9px 20px; border-radius: 8px; text-decoration: none;
    white-space: nowrap; transition: opacity .2s;
}
.race-reminder-btn:hover { opacity: .85; color: #1a0a00; }

/* ── FOOTER ── */
.race-footer-quote {
    text-align: center;
    margin-top: 56px;
    font-size: 1rem;
    color: rgba(255,255,255,0.25);
    font-style: italic;
}

/* ── ANIMATIONS ── */
@keyframes raceFadeDown {
    from { opacity: 0; transform: translateY(-24px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes raceFadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes raceShimmer {
    0%   { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}
@keyframes raceFloat {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-5px); }
}
@keyframes racePulseGlow {
    0%, 100% { box-shadow: 0 0 14px rgba(245,200,66,.6); }
    50%      { box-shadow: 0 0 24px rgba(245,200,66,.9), 0 0 40px rgba(245,200,66,.3); }
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .race-columns { grid-template-columns: 1fr; gap: 40px 0; }
    .race-col-divider { display: none; }
    .race-section { padding: 0; }
    .race-podium { gap: 4px; }
    .race-podium-slot { width: 140px; }
    .race-avatar-lg { width: 90px; height: 90px; font-size: 1.6rem; }
    .race-avatar-md { width: 70px; height: 70px; font-size: 1.2rem; }
    .race-pedestal-1 { height: 110px; }
    .race-pedestal-2 { height: 85px; }
    .race-pedestal-3 { height: 70px; }
    .sales-race-board { margin: -1rem; }
    .race-wrapper { padding: 20px 16px 60px; }
}
</style>

@push('scripts')
<script>
(function(){
    const canvas = document.getElementById('raceStars');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let stars = [];

    function resize() {
        const board = canvas.closest('.sales-race-board');
        if (!board) return;
        canvas.width = board.offsetWidth;
        canvas.height = board.offsetHeight;
    }

    function init() {
        resize();
        stars = [];
        for (let i = 0; i < 200; i++) {
            stars.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                r: Math.random() * 1.2 + 0.2,
                a: Math.random(),
                da: (Math.random() - 0.5) * 0.008
            });
        }
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        stars.forEach(s => {
            s.a += s.da;
            if (s.a <= 0 || s.a >= 1) s.da *= -1;
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255,255,255,${s.a * 0.85})`;
            ctx.fill();
        });
        requestAnimationFrame(draw);
    }

    init();
    draw();
    window.addEventListener('resize', () => { resize(); });
    document.addEventListener('livewire:navigated', () => { init(); draw(); });
})();
</script>
@endpush
