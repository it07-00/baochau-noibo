<div class="row g-4 mb-4">
    <!-- Cột trái: Tổng quan vận hành -->
    <div class="col-lg-8">
        <div class="card op-card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-body mb-1">Tổng quan vận hành</h5>
                        <div class="text-muted small">Theo dõi lịch công tác và báo cáo nội bộ trong ngày.</div>
                    </div>
                    <span class="op-office-badge">Bảo Châu Office</span>
                </div>

                <!-- KPI Boxes -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="op-stat-box">
                            <div class="d-flex align-items-center gap-3">
                                <div class="op-stat-circle op-circle-warning">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Lịch tuần này</div>
                                    <div class="op-stat-num">{{ number_format($workScheduleWeekCount) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="op-stat-box">
                            <div class="d-flex align-items-center gap-3 w-100">
                                <div class="op-stat-circle op-circle-success">
                                    <i class="fa-solid fa-file-bar-graph"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-muted small fw-semibold">Tỷ lệ báo cáo hôm nay</div>
                                    <div class="d-flex align-items-baseline justify-content-between">
                                        <span class="op-stat-num">{{ $dailyReportRate }}%</span>
                                        <span class="small text-muted">{{ $reportedTodayCount }} đã nộp, {{ $unreportedTodayCount }} chưa nộp</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Renewal radar -->
                @if(auth()->user()->hasAnyRole([\App\Enums\Role::KINH_DOANH->value, \App\Enums\Role::TP_KINH_DOANH->value, \App\Enums\Role::GIAM_DOC->value]))
                <div class="op-renewal-panel mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="op-stat-circle op-circle-warning" style="width: 32px; height: 32px; font-size: 13px;">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </div>
                            <div class="min-w-0">
                                <h6 class="fw-bold text-dark mb-0">Hợp đồng sắp tái ký</h6>
                                <div class="text-muted" style="font-size: 11px;">Theo ngày ký cũ, nhắc trước 30 ngày</div>
                            </div>
                        </div>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $upcomingRenewalContracts->count() }} hợp đồng</span>
                    </div>
                    <div class="op-renewal-list">
                        @forelse($upcomingRenewalContracts as $contract)
                            <a href="{{ $contract['url'] }}" class="op-renewal-item">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="fw-bold text-dark text-truncate small">{{ $contract['customer'] }}</span>
                                        <span class="badge bg-light text-muted border fw-normal flex-shrink-0">{{ $contract['type'] }}</span>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 11px;">
                                        {{ $contract['contract_number'] }} · Ký {{ $contract['signed_at']->format('d/m/Y') }} · Tái ký {{ $contract['renewal_date']->format('d/m/Y') }} · {{ $contract['staff'] }}
                                    </div>
                                </div>
                                <span class="op-renewal-days">{{ $contract['days_label'] }}</span>
                            </a>
                        @empty
                            <div class="op-empty-state py-3">
                                <i class="fa-solid fa-check-circle"></i>
                                <div>Chưa có hợp đồng sắp đến kỳ tái ký.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- Columns: Lịch công tác & Báo cáo mới nhất -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-dark mb-0">Lịch công tác sắp tới</h6>
                            <a href="{{ route('app.work-schedules.index') }}" class="text-decoration-none small fw-bold text-primary">Xem tất cả</a>
                        </div>
                        <div class="op-list-container">
                            @forelse($upcomingSchedules as $schedule)
                                <div class="op-list-item">
                                    <div class="op-stat-circle op-circle-warning" style="width: 32px; height: 32px; font-size: 13px;">
                                        <i class="fa-solid fa-calendar-day"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-dark text-truncate small" title="{{ $schedule->title }}">{{ $schedule->title }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ $schedule->start_date->format('d/m/Y') }} · {{ $schedule->user?->name ?? 'Hệ thống' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="op-empty-state">
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                    <div>Chưa có lịch công tác sắp tới.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-dark mb-0">Báo cáo mới nhất</h6>
                            @can('daily-reports.view')
                            <a href="{{ route('app.daily-reports.index') }}" class="text-decoration-none small fw-bold text-primary">Xem tất cả</a>
                            @endcan
                        </div>
                        <div class="op-list-container">
                            @forelse($latestReports as $report)
                                <div class="op-list-item">
                                    <x-user-avatar :user="$report->user" :size="28" class="flex-shrink-0" />
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-dark text-truncate small">{{ $report->user?->name ?? 'Hệ thống' }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Báo cáo ngày {{ $report->date->format('d/m/Y') }} · {{ $report->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="op-empty-state">
                                    <i class="fa-solid fa-file-x"></i>
                                    <div>Chưa có báo cáo nào.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Thao tác nhanh & Phân bổ vai trò -->
    <div class="col-lg-4 d-flex flex-column gap-4">
        <!-- Thao tác nhanh -->
        <div class="card op-card">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-3">Thao tác nhanh</h6>
                <div class="row g-2">
                    @can('hr-profiles.view')
                    <div class="col-6">
                        <a href="{{ route('app.hr.index') }}" class="op-quick-btn op-btn-primary">
                            <i class="fa-solid fa-users-fill"></i> Nhân sự
                        </a>
                    </div>
                    @endcan
                    <div class="col-6">
                        <a href="{{ route('app.work-schedules.index') }}" class="op-quick-btn op-btn-warning">
                            <i class="fa-solid fa-calendar-days"></i> Lịch
                        </a>
                    </div>
                    @can('daily-reports.view')
                    <div class="col-6">
                        <a href="{{ route('app.daily-reports.index') }}" class="op-quick-btn op-btn-success">
                            <i class="fa-solid fa-file-bar-graph"></i> Báo cáo
                        </a>
                    </div>
                    @endcan
                    @can('roles.view')
                    <div class="col-6">
                        <a href="{{ route('app.roles.index') }}" class="op-quick-btn op-btn-danger">
                            <i class="fa-solid fa-shield-halved"></i> Quyền
                        </a>
                    </div>
                    @endcan
                    @can('settings.view')
                    <div class="col-12">
                        <a href="{{ route('app.settings.index') }}" class="op-quick-btn op-btn-info justify-content-center">
                            <i class="fa-solid fa-gear-fill"></i> Thiết lập hệ thống
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>

        @if(auth()->user()->hasAnyRole([\App\Enums\Role::IT->value, \App\Enums\Role::GIAM_DOC->value, \App\Enums\Role::HCNS->value]))
        <!-- Phân bổ vai trò -->
        <div class="card op-card flex-grow-1">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0">Phân bổ vai trò</h6>
                    <span class="badge bg-soft-primary text-primary px-2 py-1" style="font-size: 11px;">{{ $totalActiveUsersCount }} users</span>
                </div>
                <div class="role-bars-list">
                    @foreach($dashboardRoleDistribution as $r)
                        <div class="role-bar-container">
                            <div class="role-bar-label">
                                <span>{{ $r['name'] }}</span>
                                <span>{{ $r['count'] }} người</span>
                            </div>
                            <div class="role-bar-track">
                                <div class="role-bar-fill" style="width: {{ $totalActiveUsersCount > 0 ? ($r['count'] / $totalActiveUsersCount) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
