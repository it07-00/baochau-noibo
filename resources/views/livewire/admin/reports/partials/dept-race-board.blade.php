
<div class="dept-race-board"
     x-data="{ mode: localStorage.getItem('dept_race_mode') || 'dark' }"
     :class="{ 'dept-mode-light': mode === 'light' }"
     x-init="$watch('mode', val => localStorage.setItem('dept_race_mode', val))">
    {{-- NEBULA BLOBS --}}
    <div class="dept-nebula dept-nebula-1"></div>
    <div class="dept-nebula dept-nebula-2"></div>
    <div class="dept-nebula dept-nebula-3"></div>

    {{-- STAR CANVAS --}}
    <canvas class="dept-stars-canvas" id="deptStars" wire:ignore></canvas>

    <div class="dept-wrapper">

        @php
            $formatRaceTitle = function($title) {
                $clean = trim(str_replace(['🏆', '📊', '👑', '📋'], '', $title));
                $isTrophy = str_contains($title, '🏆') || str_contains(mb_strtolower($title), 'doanh số') || str_contains(mb_strtolower($title), 'số hđ');
                if ($isTrophy) {
                    $icon = '<svg class="dept-svg-icon dept-svg-trophy me-2" viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V18H8v2h8v-2h-3v-2.1c2.16-.42 3.84-2.11 4.39-4.36A5.006 5.006 0 0 0 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>';
                } else {
                    $icon = '<svg class="dept-svg-icon dept-svg-chart me-2" viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true"><path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zM16.2 13H19v6h-2.8z"/></svg>';
                }
                return '<span class="d-inline-flex align-items-center justify-content-center">' . $icon . '<span>' . e($clean) . '</span></span>';
            };
        @endphp

        {{-- DAILY REPORT REMINDER (chỉ hiện nếu chưa gửi báo cáo ngày) --}}
        @if(!$this->hasDailyReportToday())
        <div class="dept-reminder">
            <div class="dept-reminder-icon text-warning">
                <svg class="dept-svg-icon" viewBox="0 0 24 24" fill="currentColor" width="26" height="26" aria-hidden="true">
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/>
                </svg>
            </div>
            <div class="dept-reminder-text">
                <strong>Bạn chưa gửi báo cáo ngày hôm nay</strong>
                <span>Vui lòng gửi báo cáo trước khi kết thúc ngày làm việc.</span>
            </div>
            <a href="{{ route('app.daily-reports.index') }}" class="dept-reminder-btn d-inline-flex align-items-center gap-1.5">
                <svg class="dept-svg-icon" viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
                <span>Gửi báo cáo</span>
            </a>
        </div>
        @endif

        {{-- FILTERS & MODE TOGGLE BAR --}}
        <div class="dept-filters d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div class="d-flex align-items-center gap-2">
                <select wire:model.live="{{ $wireYearModel ?? 'year' }}" class="dept-select">
                    @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 2 Mode Toggle Buttons --}}
            <div class="dept-mode-toggle p-1 d-inline-flex gap-1">
                <button type="button" @click="mode = 'dark'"
                        class="btn btn-sm px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5"
                        :class="mode === 'dark' ? 'dept-mode-btn-active' : 'dept-mode-btn-inactive'">
                    <i class="fa-solid fa-moon"></i>
                    <span>Tối</span>
                </button>
                <button type="button" @click="mode = 'light'"
                        class="btn btn-sm px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5"
                        :class="mode === 'light' ? 'dept-mode-btn-active' : 'dept-mode-btn-inactive'">
                    <i class="fa-solid fa-sun"></i>
                    <span>Sáng</span>
                </button>
            </div>
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
                <div class="dept-col-title">{!! $formatRaceTitle($colLeftTitle) !!}</div>

                @if($rateRankings->isEmpty())
                    <div class="dept-empty">Không có dữ liệu</div>
                @else
                    @if($rateRankings->count() >= 3)
                    <div class="dept-podium">
                        {{-- #2 --}}
                        <div class="dept-podium-slot dept-podium-2">
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-md dept-border-silver">
                                    @if($rateRankings[1]['avatar_url'])
                                        <img src="{{ $rateRankings[1]['avatar_url'] }}" alt="{{ $rateRankings[1]['name'] }}">
                                    @else
                                        {{ $this->deptInitials($rateRankings[1]['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-silver">2</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-2">
                                <div class="dept-podium-name">{{ $rateRankings[1]['name'] }}</div>
                                <div class="dept-podium-value">{{ $rateRankings[1]['pct'] }}%</div>
                                <div class="dept-podium-sub">{{ $rateRankings[1]['finished'] }}/{{ $rateRankings[1]['total'] }} HĐ</div>
                            </div>
                        </div>

                        {{-- #1 --}}
                        <div class="dept-podium-slot dept-podium-1">
                            <div class="dept-crown" title="Quán quân">
                                <svg class="dept-svg-icon dept-svg-crown" viewBox="0 0 24 24" fill="currentColor" width="28" height="28" aria-hidden="true">
                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
                                </svg>
                            </div>
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-lg dept-border-gold">
                                    @if($rateRankings[0]['avatar_url'])
                                        <img src="{{ $rateRankings[0]['avatar_url'] }}" alt="{{ $rateRankings[0]['name'] }}">
                                    @else
                                        {{ $this->deptInitials($rateRankings[0]['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-gold">1</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-1">
                                <div class="dept-podium-name dept-name-gold">{{ $rateRankings[0]['name'] }}</div>
                                <div class="dept-podium-value dept-value-gold">{{ $rateRankings[0]['pct'] }}%</div>
                                <div class="dept-podium-sub">{{ $rateRankings[0]['finished'] }}/{{ $rateRankings[0]['total'] }} HĐ</div>
                            </div>
                        </div>

                        {{-- #3 --}}
                        <div class="dept-podium-slot dept-podium-3">
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-md dept-border-bronze">
                                    @if($rateRankings[2]['avatar_url'])
                                        <img src="{{ $rateRankings[2]['avatar_url'] }}" alt="{{ $rateRankings[2]['name'] }}">
                                    @else
                                        {{ $this->deptInitials($rateRankings[2]['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-bronze">3</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-3">
                                <div class="dept-podium-name">{{ $rateRankings[2]['name'] }}</div>
                                <div class="dept-podium-value">{{ $rateRankings[2]['pct'] }}%</div>
                                <div class="dept-podium-sub">{{ $rateRankings[2]['finished'] }}/{{ $rateRankings[2]['total'] }} HĐ</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach($rateRankings->values() as $i => $row)
                        @if($rateRankings->count() >= 3 && $i < 3) @continue @endif
                        <div class="dept-rank-card">
                            <div class="dept-rank-num">{{ $i + 1 }}.</div>
                            <div class="dept-avatar dept-avatar-sm">
                                @if($row['avatar_url'])
                                    <img src="{{ $row['avatar_url'] }}" alt="{{ $row['name'] }}">
                                @else
                                    {{ $this->deptInitials($row['name']) }}
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
                <div class="dept-col-title">{!! $formatRaceTitle($colRightTitle) !!}</div>

                @if($completionRankings->isEmpty())
                    <div class="dept-empty">Không có dữ liệu</div>
                @else
                    @if($completionRankings->count() >= 3)
                    <div class="dept-podium">
                        {{-- #2 --}}
                        <div class="dept-podium-slot dept-podium-2">
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-md dept-border-silver">
                                    @if($completionRankings[1]['avatar_url'])
                                        <img src="{{ $completionRankings[1]['avatar_url'] }}" alt="{{ $completionRankings[1]['name'] }}">
                                    @else
                                        {{ $this->deptInitials($completionRankings[1]['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-silver">2</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-2">
                                <div class="dept-podium-name">{{ $completionRankings[1]['name'] }}</div>
                                <div class="dept-podium-value">{{ $completionRankings[1]['finished'] }} HĐ</div>
                                <div class="dept-podium-sub">/ {{ $completionRankings[1]['total'] }} tổng</div>
                            </div>
                        </div>

                        {{-- #1 --}}
                        <div class="dept-podium-slot dept-podium-1">
                            <div class="dept-crown" title="Quán quân">
                                <svg class="dept-svg-icon dept-svg-crown" viewBox="0 0 24 24" fill="currentColor" width="28" height="28" aria-hidden="true">
                                    <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
                                </svg>
                            </div>
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-lg dept-border-gold">
                                    @if($completionRankings[0]['avatar_url'])
                                        <img src="{{ $completionRankings[0]['avatar_url'] }}" alt="{{ $completionRankings[0]['name'] }}">
                                    @else
                                        {{ $this->deptInitials($completionRankings[0]['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-gold">1</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-1">
                                <div class="dept-podium-name dept-name-gold">{{ $completionRankings[0]['name'] }}</div>
                                <div class="dept-podium-value dept-value-gold">{{ $completionRankings[0]['finished'] }} HĐ</div>
                                <div class="dept-podium-sub">/ {{ $completionRankings[0]['total'] }} tổng</div>
                            </div>
                        </div>

                        {{-- #3 --}}
                        <div class="dept-podium-slot dept-podium-3">
                            <div class="dept-avatar-wrap">
                                <div class="dept-avatar dept-avatar-md dept-border-bronze">
                                    @if($completionRankings[2]['avatar_url'])
                                        <img src="{{ $completionRankings[2]['avatar_url'] }}" alt="{{ $completionRankings[2]['name'] }}">
                                    @else
                                        {{ $this->deptInitials($completionRankings[2]['name']) }}
                                    @endif
                                </div>
                                <div class="dept-medal dept-medal-bronze">3</div>
                            </div>
                            <div class="dept-pedestal dept-pedestal-3">
                                <div class="dept-podium-name">{{ $completionRankings[2]['name'] }}</div>
                                <div class="dept-podium-value">{{ $completionRankings[2]['finished'] }} HĐ</div>
                                <div class="dept-podium-sub">/ {{ $completionRankings[2]['total'] }} tổng</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach($completionRankings->values() as $i => $row)
                        @if($completionRankings->count() >= 3 && $i < 3) @continue @endif
                        <div class="dept-rank-card">
                            <div class="dept-rank-num">{{ $i + 1 }}.</div>
                            <div class="dept-avatar dept-avatar-sm">
                                @if($row['avatar_url'])
                                    <img src="{{ $row['avatar_url'] }}" alt="{{ $row['name'] }}">
                                @else
                                    {{ $this->deptInitials($row['name']) }}
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
