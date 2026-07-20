<div class="sales-race-board" x-data>
    {{-- NEBULA BLOBS --}}
    <div class="race-nebula race-nebula-1"></div>
    <div class="race-nebula race-nebula-2"></div>
    <div class="race-nebula race-nebula-3"></div>

    {{-- STAR CANVAS --}}
    <canvas class="race-stars-canvas" id="raceStars" wire:ignore></canvas>

    <div class="race-wrapper">

        {{-- DAILY REPORT REMINDER --}}
        @if (!$this->salesHasDailyReport())
            <div class="race-reminder">
                <div class="race-reminder-icon text-warning">
                    <svg class="dept-svg-icon" viewBox="0 0 24 24" fill="currentColor" width="26" height="26" aria-hidden="true">
                        <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    </svg>
                </div>
                <div class="race-reminder-text">
                    <strong>Bạn chưa gửi báo cáo ngày hôm nay</strong>
                    <span>Vui lòng gửi báo cáo trước khi kết thúc ngày làm việc.</span>
                </div>
                <a href="{{ route('app.daily-reports.index') }}" class="race-reminder-btn">Gửi báo cáo</a>
            </div>
        @endif

        {{-- FILTERS --}}
        <div class="race-filters" wire:loading.class="race-filters-loading">
            <label class="visually-hidden" for="race-month">Tháng báo cáo</label>
            <select id="race-month" wire:model.live="filter_month" wire:loading.attr="disabled" class="race-select" aria-label="Tháng báo cáo">
                <option value="">Cả năm</option>
                @foreach ($months as $m)
                    <option value="{{ $m }}">Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                @endforeach
            </select>
            <label class="visually-hidden" for="race-year">Năm báo cáo</label>
            <select id="race-year" wire:model.live="year" wire:loading.attr="disabled" class="race-select" aria-label="Năm báo cáo">
                @foreach ($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        {{-- COMPANY PROGRESS BAR --}}
        @php($companyRemaining = max($companyTarget - $companyActual, 0))
        <div class="race-company-progress" aria-label="Tiến độ doanh số toàn công ty">
            <div class="race-progress-summary">
                <div>
                    <span>Doanh số hiện tại</span>
                    <strong>{{ number_format($companyActual, 0, ',', '.') }}đ</strong>
                </div>
                <div>
                    <span>Còn lại để đạt mục tiêu</span>
                    <strong>{{ number_format($companyRemaining, 0, ',', '.') }}đ</strong>
                </div>
            </div>
            @if ($companyTarget > 0)
                <div class="race-progress-track">
                    <div class="race-progress-bar-wrap">
                        <div class="race-progress-fill" style="width: {{ min($companyPct, 100) }}%"></div>
                        <div class="race-progress-label" style="left: {{ min($companyPct, 100) }}%; transform: translateX({{ $companyPct <= 5 ? '0%' : ($companyPct >= 95 ? '-100%' : '-50%') }})">
                            {{ number_format($companyActual, 0, ',', '.') }}đ ({{ $companyPct }}%)
                        </div>
                        <div class="race-progress-markers" aria-hidden="true"><span>25%</span><span>50%</span><span>75%</span><span>100%</span></div>
                    </div>
                    <div class="race-progress-end">
                        <span>{{ number_format($companyTarget, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            @else
                <div class="race-progress-empty-state">
                    <i class="fa-solid fa-bullseye" aria-hidden="true"></i>
                    <span>Chưa thiết lập chỉ tiêu cho kỳ này</span>
                </div>
            @endif
        </div>

        {{-- HEADER --}}
        <div class="race-header">
            <div class="race-hero-kicker"><i class="fa-solid fa-trophy" aria-hidden="true"></i><span>Bảng vinh danh doanh số</span></div>
            <div class="race-header-badge">
                Đường Đua Doanh Số – Chiến Binh Bảo Châu – {{ $this->monthLabel() }}/{{ $year }}
            </div>
            <div class="race-header-sub">"Mỗi ngày nỗ lực · mỗi con số là một dấu ấn"</div>
        </div>
        <div class="race-gold-divider"></div>

        {{-- COLUMNS --}}
        <div class="race-columns">

            {{-- LEFT: DOANH SỐ --}}
            <div class="race-section">
                <div class="race-col-title">
                    <span class="d-inline-flex align-items-center justify-content-center">
                        <svg class="dept-svg-icon dept-svg-trophy me-2" viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true">
                            <path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.01 5.01 0 0 0 11 15.9V18H8v2h8v-2h-3v-2.1c2.16-.42 3.84-2.11 4.39-4.36A5.006 5.006 0 0 0 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
                        </svg>
                        <span>Đường Đua Doanh Số</span>
                    </span>
                </div>

                @if ($doanhSoRankings->isEmpty())
                    <div class="race-empty">Không có dữ liệu</div>
                @else
                    {{-- TOP 3 PODIUM WITH PEDESTALS --}}
                    @if ($doanhSoRankings->count() >= 3)
                        <div class="race-podium">
                            {{-- #2 LEFT --}}
                            <div class="race-podium-slot race-podium-2">
                                <div class="race-avatar-wrap">
                                    <div class="race-avatar race-avatar-md">
                                        @if ($doanhSoRankings[1]['avatar_url'])
                                            <img src="{{ $doanhSoRankings[1]['avatar_url'] }}" alt="{{ $doanhSoRankings[1]['name'] }}">
                                        @else
                                            {{ $this->raceInitials($doanhSoRankings[1]['name']) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="race-pedestal race-pedestal-2">
                                    <img src="{{ asset('assets/images/2.png') }}" class="race-medal-img"
                                        alt="Hạng 2">
                                    <div class="race-podium-name">{{ $doanhSoRankings[1]['name'] }}</div>
                                    <div class="race-podium-value">{{ number_format($doanhSoRankings[1]['total'], 0, ',', '.') }}đ</div>
                                </div>
                            </div>

                            {{-- #1 CENTER --}}
                            <div class="race-podium-slot race-podium-1">
                                <div class="race-crown" title="Quán quân">
                                    <svg class="dept-svg-icon dept-svg-crown" viewBox="0 0 24 24" fill="currentColor" width="28" height="28" aria-hidden="true">
                                        <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
                                    </svg>
                                </div>
                                <div class="race-avatar-wrap">
                                    <div class="race-avatar race-avatar-lg">
                                        @if ($doanhSoRankings[0]['avatar_url'])
                                            <img src="{{ $doanhSoRankings[0]['avatar_url'] }}" alt="{{ $doanhSoRankings[0]['name'] }}">
                                        @else
                                            {{ $this->raceInitials($doanhSoRankings[0]['name']) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="race-pedestal race-pedestal-1">
                                    <img src="{{ asset('assets/images/1.png') }}" class="race-medal-img"
                                        alt="Hạng 1">
                                    <div class="race-podium-name race-name-gold">{{ $doanhSoRankings[0]['name'] }}</div>
                                    <div class="race-podium-value race-value-gold">
                                        {{ number_format($doanhSoRankings[0]['total'], 0, ',', '.') }}đ</div>
                                </div>
                            </div>

                            {{-- #3 RIGHT --}}
                            <div class="race-podium-slot race-podium-3">
                                <div class="race-avatar-wrap">
                                    <div class="race-avatar race-avatar-md">
                                        @if ($doanhSoRankings[2]['avatar_url'])
                                            <img src="{{ $doanhSoRankings[2]['avatar_url'] }}" alt="{{ $doanhSoRankings[2]['name'] }}">
                                        @else
                                            {{ $this->raceInitials($doanhSoRankings[2]['name']) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="race-pedestal race-pedestal-3">
                                    <img src="{{ asset('assets/images/3.png') }}" class="race-medal-img"
                                        alt="Hạng 3">
                                    <div class="race-podium-name">{{ $doanhSoRankings[2]['name'] }}</div>
                                    <div class="race-podium-value">{{ number_format($doanhSoRankings[2]['total'], 0, ',', '.') }}đ</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- REMAINING RANKS --}}
                    @foreach ($doanhSoRankings->skip(3)->values() as $i => $row)
                        <div class="race-rank-card">
                            <div class="race-rank-num">{{ $i + 4 }}.</div>
                            <div class="race-avatar race-avatar-sm">
                                @if ($row['avatar_url'])
                                    <img src="{{ $row['avatar_url'] }}" alt="{{ $row['name'] }}">
                                @else
                                    {{ $this->raceInitials($row['name']) }}
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
                <div class="race-col-title">
                    <span class="d-inline-flex align-items-center justify-content-center">
                        <svg class="dept-svg-icon dept-svg-chart me-2" viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true">
                            <path d="M5 9.2h3V19H5zM10.6 5h2.8v14h-2.8zM16.2 13H19v6h-2.8z"/>
                        </svg>
                        <span>Đường Đua KPI</span>
                    </span>
                </div>

                @if (! $hasKpiTarget)
                    <div class="race-empty">
                        <i class="fa-solid fa-bullseye d-block mb-2 fs-4" aria-hidden="true"></i>
                        Chưa thiết lập chỉ tiêu KPI cho kỳ này
                    </div>
                @elseif ($kpiRankings->isEmpty())
                    <div class="race-empty">Không có dữ liệu</div>
                @else
                    {{-- TOP 3 PODIUM WITH PEDESTALS --}}
                    @if ($kpiRankings->count() >= 3)
                        <div class="race-podium">
                            {{-- #2 LEFT --}}
                            <div class="race-podium-slot race-podium-2">
                                <div class="race-avatar-wrap">
                                    <div class="race-avatar race-avatar-md">
                                        @if ($kpiRankings[1]['avatar_url'])
                                            <img src="{{ $kpiRankings[1]['avatar_url'] }}" alt="{{ $kpiRankings[1]['name'] }}">
                                        @else
                                            {{ $this->raceInitials($kpiRankings[1]['name']) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="race-pedestal race-pedestal-2">
                                    <img src="{{ asset('assets/images/2.png') }}" class="race-medal-img"
                                        alt="Hạng 2">
                                    <div class="race-podium-name">{{ $kpiRankings[1]['name'] }}</div>
                                    <div class="race-podium-value">{{ $kpiRankings[1]['pct'] }}%</div>
                                </div>
                            </div>

                            {{-- #1 CENTER --}}
                            <div class="race-podium-slot race-podium-1">
                                <div class="race-crown" title="Quán quân">
                                    <svg class="dept-svg-icon dept-svg-crown" viewBox="0 0 24 24" fill="currentColor" width="28" height="28" aria-hidden="true">
                                        <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
                                    </svg>
                                </div>
                                <div class="race-avatar-wrap">
                                    <div class="race-avatar race-avatar-lg">
                                        @if ($kpiRankings[0]['avatar_url'])
                                            <img src="{{ $kpiRankings[0]['avatar_url'] }}" alt="{{ $kpiRankings[0]['name'] }}">
                                        @else
                                            {{ $this->raceInitials($kpiRankings[0]['name']) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="race-pedestal race-pedestal-1">
                                    <img src="{{ asset('assets/images/1.png') }}" class="race-medal-img"
                                        alt="Hạng 1">
                                    <div class="race-podium-name race-name-gold">{{ $kpiRankings[0]['name'] }}</div>
                                    <div class="race-podium-value race-value-gold">{{ $kpiRankings[0]['pct'] }}%</div>
                                </div>
                            </div>

                            {{-- #3 RIGHT --}}
                            <div class="race-podium-slot race-podium-3">
                                <div class="race-avatar-wrap">
                                    <div class="race-avatar race-avatar-md">
                                        @if ($kpiRankings[2]['avatar_url'])
                                            <img src="{{ $kpiRankings[2]['avatar_url'] }}" alt="{{ $kpiRankings[2]['name'] }}">
                                        @else
                                            {{ $this->raceInitials($kpiRankings[2]['name']) }}
                                        @endif
                                    </div>
                                </div>
                                <div class="race-pedestal race-pedestal-3">
                                    <img src="{{ asset('assets/images/3.png') }}" class="race-medal-img"
                                        alt="Hạng 3">
                                    <div class="race-podium-name">{{ $kpiRankings[2]['name'] }}</div>
                                    <div class="race-podium-value">{{ $kpiRankings[2]['pct'] }}%</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- REMAINING RANKS --}}
                    @foreach ($kpiRankings->skip(3)->values() as $i => $row)
                        <div class="race-rank-card">
                            <div class="race-rank-num">{{ $i + 4 }}.</div>
                            <div class="race-avatar race-avatar-sm">
                                @if ($row['avatar_url'])
                                    <img src="{{ $row['avatar_url'] }}" alt="{{ $row['name'] }}">
                                @else
                                    {{ $this->raceInitials($row['name']) }}
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

@push('scripts')
    <script>
        (function() {
            const canvas = document.getElementById('raceStars');
            if (!canvas) return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            if (window.salesRaceStars?.frameId) {
                cancelAnimationFrame(window.salesRaceStars.frameId);
            }

            const ctx = canvas.getContext('2d');
            let stars = [];
            let frameId = null;

            function resize() {
                const board = canvas.closest('.sales-race-board');
                if (!board) return;
                canvas.width = board.offsetWidth;
                canvas.height = board.offsetHeight;
            }

            function init() {
                resize();
                stars = [];
                const count = window.innerWidth < 768 ? 36 : 72;
                for (let i = 0; i < count; i++) {
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
                if (document.hidden) {
                    frameId = null;
                    return;
                }
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                stars.forEach(s => {
                    s.a += s.da;
                    if (s.a <= 0 || s.a >= 1) s.da *= -1;
                    ctx.beginPath();
                    ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(255,255,255,${s.a * 0.85})`;
                    ctx.fill();
                });
                frameId = requestAnimationFrame(draw);
                window.salesRaceStars.frameId = frameId;
            }

            init();
            window.salesRaceStars = { frameId: null };
            draw();
            window.addEventListener('resize', () => {
                resize();
            });
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && !frameId) {
                    draw();
                }
            });
        })();
    </script>
@endpush
