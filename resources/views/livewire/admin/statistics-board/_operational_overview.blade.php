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
                                    <i class="bi bi-calendar3"></i>
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
                                    <i class="bi bi-file-earmark-bar-graph"></i>
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
                                        <i class="bi bi-calendar-event"></i>
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
                                    <i class="bi bi-calendar-x"></i>
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
                                    <div class="op-user-avatar" style="background-color: {{ '#' . substr(md5($report->user?->name ?? 'A'), 0, 6) }}; color: #ffffff;">
                                        {{ $report->user ? strtoupper(substr($report->user->name, 0, 1)) : '?' }}
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-dark text-truncate small">{{ $report->user?->name ?? 'Hệ thống' }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Báo cáo ngày {{ $report->date->format('d/m/Y') }} · {{ $report->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="op-empty-state">
                                    <i class="bi bi-file-earmark-x"></i>
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
                            <i class="bi bi-people-fill"></i> Nhân sự
                        </a>
                    </div>
                    @endcan
                    <div class="col-6">
                        <a href="{{ route('app.work-schedules.index') }}" class="op-quick-btn op-btn-warning">
                            <i class="bi bi-calendar3"></i> Lịch
                        </a>
                    </div>
                    @can('daily-reports.view')
                    <div class="col-6">
                        <a href="{{ route('app.daily-reports.index') }}" class="op-quick-btn op-btn-success">
                            <i class="bi bi-file-earmark-bar-graph"></i> Báo cáo
                        </a>
                    </div>
                    @endcan
                    @can('roles.view')
                    <div class="col-6">
                        <a href="{{ route('app.roles.index') }}" class="op-quick-btn op-btn-danger">
                            <i class="bi bi-shield-lock-fill"></i> Quyền
                        </a>
                    </div>
                    @endcan
                    @can('settings.view')
                    <div class="col-12">
                        <a href="{{ route('app.settings.index') }}" class="op-quick-btn op-btn-info justify-content-center">
                            <i class="bi bi-gear-fill"></i> Thiết lập hệ thống
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
