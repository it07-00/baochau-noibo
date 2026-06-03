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
    {{-- NEBULA BLOBS --}}
    <div class="dept-nebula dept-nebula-1"></div>
    <div class="dept-nebula dept-nebula-2"></div>
    <div class="dept-nebula dept-nebula-3"></div>

    {{-- STAR CANVAS --}}
    <canvas class="dept-stars-canvas" id="deptStars" wire:ignore></canvas>

    <div class="dept-wrapper">

        {{-- DAILY REPORT REMINDER (dark-themed, chỉ hiện nếu chưa gửi báo cáo ngày) --}}
        @if(!$this->hasDailyReportToday())
        <div class="dept-reminder">
            <div class="dept-reminder-icon">⏰</div>
            <div class="dept-reminder-text">
                <strong>Bạn chưa gửi báo cáo ngày hôm nay</strong>
                <span>Vui lòng gửi báo cáo trước khi kết thúc ngày làm việc.</span>
            </div>
            <a href="{{ route('app.daily-reports.index') }}" class="dept-reminder-btn">
                ✏️ Gửi báo cáo
            </a>
        </div>
        @endif

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
                            <div class="dept-crown">👑</div>
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
                <div class="dept-col-title">{{ $colRightTitle }}</div>

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
                            <div class="dept-crown">👑</div>
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
