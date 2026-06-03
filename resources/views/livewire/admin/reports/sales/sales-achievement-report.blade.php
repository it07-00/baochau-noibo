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
                @foreach ($months as $m)
                    <option value="{{ $m }}">Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                @endforeach
            </select>
            <select wire:model.live="year" class="race-select">
                @foreach ($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        {{-- COMPANY PROGRESS BAR --}}
        <div class="race-company-progress">
            <div class="race-progress-track">
                <div class="race-progress-bar-wrap">
                    <div class="race-progress-fill" style="width: {{ min($companyPct, 100) }}%"></div>
                    <div class="race-progress-label" style="left: {{ min($companyPct, 100) }}%; transform: translateX({{ $companyPct <= 5 ? '0%' : ($companyPct >= 95 ? '-100%' : '-50%') }})">
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
                Đường Đua Doanh Số – Chiến Binh Bảo Châu – {{ $this->monthLabel() }}/{{ $year }}
            </div>
            <div class="race-header-sub">"Mỗi ngày nỗ lực · mỗi con số là một dấu ấn"</div>
        </div>
        <div class="race-gold-divider"></div>

        {{-- COLUMNS --}}
        <div class="race-columns">

            {{-- LEFT: DOANH SỐ --}}
            <div class="race-section">
                <div class="race-col-title">🏆 Đường Đua Doanh Số</div>

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
                                <div class="race-crown">👑</div>
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
                <div class="race-col-title">📊 Đường Đua KPI</div>

                @if ($kpiRankings->isEmpty())
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
                                <div class="race-crown">👑</div>
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
            window.addEventListener('resize', () => {
                resize();
            });
            document.addEventListener('livewire:navigated', () => {
                init();
                draw();
            });
        })();
    </script>
@endpush
