{{-- Shared Calendar Grid (Desktop & Mobile) --}}
<div>
    {{-- Mobile View --}}
    <div class="daily-report-mobile-agenda">
        <div class="daily-report-mobile-agenda-summary">
            <div>
                <div class="daily-report-mobile-agenda-title">Tháng {{ (int) $monthFilter }}/{{ $yearFilter }}</div>
                <div class="small text-muted">{{ $this->mobileReportDays($calendarData)->count() }} ngày có báo cáo
                </div>
            </div>
            <span
                class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">{{ $reportStats['total'] }}
                báo cáo</span>
        </div>

        @forelse($this->mobileReportDays($calendarData) as $day)
            <button type="button" class="daily-report-mobile-day hover-lift"
                @click="$dispatch('open-day-detail', { date: '{{ $day['date']->format('d/m/Y') }}', reports: {{ $this->reportPayload($day['reports']) }} })">
                <div class="daily-report-mobile-date">
                    <div class="daily-report-mobile-weekday">{{ Str::upper($day['date']->isoFormat('dd')) }}</div>
                    <div class="daily-report-mobile-daynum">{{ $day['date']->format('d') }}</div>
                </div>
                <div class="daily-report-mobile-day-body">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="fw-bold text-dark">{{ $day['reports']->count() }} báo cáo</span>
                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                    </div>
                    <div class="daily-report-mobile-chip-row">
                        @if ($this->dayIssueCount($day['reports']) > 0)
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5"
                                style="font-size: 0.7rem;">{{ $this->dayIssueCount($day['reports']) }} cần hỗ trợ</span>
                        @endif
                        @if ($this->dayLateCount($day['reports']) > 0)
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5"
                                style="font-size: 0.7rem;">{{ $this->dayLateCount($day['reports']) }} trễ</span>
                        @endif
                        @if ($this->dayIssueCount($day['reports']) === 0 && $this->dayLateCount($day['reports']) === 0)
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5"
                                style="font-size: 0.7rem;">Hoàn thành</span>
                        @endif
                    </div>
                    <div class="daily-report-mobile-names text-muted">
                        {{ $this->dayNamesPreview($day['reports']) }}
                    </div>
                </div>
            </button>
        @empty
            <div class="daily-report-mobile-empty">
                <i class="fa-solid fa-calendar-xmark"></i>
                <span class="small fw-semibold">Chưa có báo cáo nào trong tháng này.</span>
            </div>
        @endforelse
    </div>

    {{-- Desktop View --}}
    <div
        class="calendar-container daily-report-desktop-calendar bg-white shadow-sm rounded-12px overflow-hidden border border-light-subtle">
        <div class="calendar-header-grid border-bottom border-light-subtle bg-light bg-opacity-70 py-2.5">
            @foreach ($this->weekdayShortNames() as $dow)
                <div class="calendar-header-cell fw-bold text-secondary text-center text-uppercase"
                    style="font-size: 0.8rem; letter-spacing: 0.05em;">
                    {{ $dow }}
                </div>
            @endforeach
        </div>

        <div class="calendar-body-grid">
            @foreach ($this->calendarPeriod() as $currentDate)
                @php
                    $dayReports = $this->dayReportsForDate($calendarData, $currentDate);
                    $isInsideMonth = $currentDate->month == (int) $monthFilter;
                    $hasReport = $isInsideMonth && $dayReports->isNotEmpty();
                    $isToday = $currentDate->isToday();
                @endphp
                <div class="calendar-day-cell position-relative border-start border-bottom border-light-subtle
                        {{ !$isInsideMonth ? 'bg-light opacity-50' : ($currentDate->isSunday() ? 'bg-sunday' : 'bg-white') }}
                        {{ $isToday ? 'border border-primary border-2 shadow-sm' : '' }}
                        {{ $hasReport ? 'cursor-pointer' : '' }}"
                    style="min-height: 140px; transition: all 0.2s ease; padding: 10px; min-width: 0; overflow: hidden; z-index: {{ $isToday ? '2' : '1' }};"
                    @if ($hasReport) onmouseover="this.style.backgroundColor='var(--bs-tertiary-bg)'"
                        onmouseout="this.style.backgroundColor=''"
                        @click="$dispatch('open-day-detail', { date: '{{ $currentDate->format('d/m/Y') }}', reports: {{ $this->reportPayload($dayReports) }} })"
                    @endif>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        @if ($isToday)
                            <span
                                class="fw-bold bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                                style="width: 26px; height: 26px; font-size: 0.8rem;">
                                {{ $currentDate->day }}
                            </span>
                        @else
                            <span class="fw-semibold text-secondary {{ $isInsideMonth ? 'text-dark' : 'text-muted' }}"
                                style="font-size: 0.85rem;">
                                {{ $currentDate->day }}
                            </span>
                        @endif

                        @if ($isInsideMonth && $dayReports->isNotEmpty())
                            <span class="rounded-circle d-inline-block bg-{{ $this->dayDotColor($currentDate, $dayReports) }}"
                                style="width: 6px; height: 6px;"></span>
                        @endif
                    </div>

                    <div class="calendar-day-content">
                        @if ($hasReport)
                            @foreach ($dayReports as $dr)
                                <div
                                    class="mb-1.5 px-2 py-1.5 rounded cal-report-chip {{ $this->reportLateDays($dr) > 0 ? 'cal-chip-late' : 'cal-chip-' . ($dr->status === 'Gặp vấn đề, cần hỗ trợ' ? 'issue' : ($dr->status === 'Hoàn thành một phần' ? 'partial' : 'done')) }} fs-clamp-sm cursor-pointer mxw-100 overflow-hidden">
                                    <div class="d-flex align-items-center justify-content-between gap-1 w-100 mb-1">
                                        <span class="fw-bold text-truncate"
                                            style="max-width: 75%; font-size: 0.72rem;">{{ $dr->user->name ?? '' }}</span>
                                        @if ($this->reportLateDays($dr) > 0)
                                            <span class="badge bg-danger text-white px-1 py-0.5 fs-60 rounded-sm">Trễ</span>
                                        @endif
                                    </div>
                                    <div class="text-truncate fs-65 text-muted opacity-80" style="font-size: 0.65rem;">
                                        {{ strip_tags($dr->content) }}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>