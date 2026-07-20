<div class="row g-4 mb-4">
    <!-- Cột trái: Tổng quan vận hành -->
    <div class="col-lg-8">
        <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-body mb-1">Tổng quan vận hành</h5>
                        <div class="text-muted small">Theo dõi lịch công tác và báo cáo nội bộ trong ngày.</div>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-7 d-inline-flex align-items-center">Bảo Châu Office</span>
                </div>

                <!-- KPI Boxes -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-warning bg-opacity-05 border border-warning-subtle rounded-3">
                            <div class="avatar bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center fs-5" style="width: 48px !important; height: 48px !important; min-width: 48px !important; flex-shrink: 0;">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Lịch tuần này</div>
                                <h4 class="mb-0 fw-bold text-body mt-1">{{ number_format($workScheduleWeekCount) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-success bg-opacity-05 border border-success-subtle rounded-3 w-100">
                            <div class="avatar bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center fs-5" style="width: 48px !important; height: 48px !important; min-width: 48px !important; flex-shrink: 0;">
                                <i class="fa-solid fa-chart-column"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted small fw-semibold">Tỷ lệ báo cáo hôm nay</div>
                                <div class="d-flex align-items-baseline justify-content-between mt-1">
                                    <h4 class="mb-0 fw-bold text-body">{{ $dailyReportRate }}%</h4>
                                    <span class="small text-muted">{{ $reportedTodayCount }} đã nộp, {{ $unreportedTodayCount }} chưa nộp</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Renewal radar -->
                @if(auth()->user()->hasAnyRole([\App\Enums\Role::KINH_DOANH->value, \App\Enums\Role::TP_KINH_DOANH->value, \App\Enums\Role::GIAM_DOC->value]))
                <div class="p-3 bg-warning-subtle bg-opacity-25 border border-warning-subtle rounded-3 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="avatar bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center fs-6" style="width: 32px !important; height: 32px !important; min-width: 32px !important; flex-shrink: 0;">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </div>
                            <div class="min-w-0">
                                <h6 class="fw-bold text-body mb-0">Hợp đồng sắp tái ký</h6>
                                <div class="text-muted" style="font-size: 11px;">Theo ngày ký cũ, nhắc trước 30 ngày</div>
                            </div>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle d-inline-flex align-items-center">{{ $upcomingRenewalContracts->count() }} hợp đồng</span>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        @forelse($upcomingRenewalContracts as $contract)
                            <a href="{{ $contract['url'] }}" class="d-flex align-items-center justify-content-between p-2 bg-warning bg-opacity-05 border border-warning-subtle rounded-2 text-decoration-none hover-lift">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="fw-bold text-body text-truncate small">{{ $contract['customer'] }}</span>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle fw-normal flex-shrink-0 d-inline-flex align-items-center">{{ $contract['type'] }}</span>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 11px;">
                                        {{ $contract['contract_number'] }} · Ký {{ $contract['signed_at']->format('d/m/Y') }} · Tái ký {{ $contract['renewal_date']->format('d/m/Y') }} · {{ $contract['staff'] }}
                                    </div>
                                </div>
                                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fs-8 flex-shrink-0 d-inline-flex align-items-center">{{ $contract['days_label'] }}</span>
                            </a>
                        @empty
                            <div class="d-flex flex-column align-items-center justify-content-center py-3 text-muted" style="font-size: 13px;">
                                <i class="fa-solid fa-check-circle text-success mb-2 fs-5"></i>
                                <div>Chưa có hợp đồng sắp đến kỳ tái ký.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- Columns: Lịch công tác & Báo cáo mới nhất -->
                <div class="row g-4">
                    <div class="col-xl-6 col-12">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-body mb-0">Lịch công tác sắp tới</h6>
                            <a href="{{ route('app.work-schedules.index') }}" class="text-decoration-none small fw-bold text-primary">Xem tất cả</a>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            @forelse($upcomingSchedules as $schedule)
                                <div class="d-flex align-items-center gap-3 p-2 bg-primary bg-opacity-05 border border-primary-subtle rounded-3">
                                    <div class="avatar bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center fs-6" style="width: 32px !important; height: 32px !important; min-width: 32px !important; flex-shrink: 0;">
                                        <i class="fa-solid fa-calendar-day"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-body text-truncate small" title="{{ $schedule->title }}">{{ $schedule->title }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ $schedule->start_date->format('d/m/Y') }} · {{ $schedule->user?->name ?? 'Hệ thống' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="d-flex flex-column align-items-center justify-content-center py-4 text-muted border border-dashed rounded-3" style="font-size: 13px;">
                                    <i class="fa-solid fa-calendar-xmark mb-2 fs-5 opacity-50"></i>
                                    <div>Chưa có lịch công tác sắp tới.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-xl-6 col-12">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-body mb-0">Báo cáo mới nhất</h6>
                            @can('daily-reports.view')
                            <a href="{{ route('app.daily-reports.index') }}" class="text-decoration-none small fw-bold text-primary">Xem tất cả</a>
                            @endcan
                        </div>
                        <div class="d-flex flex-column gap-2">
                            @forelse($latestReports as $report)
                                <div class="d-flex align-items-center gap-3 p-2 bg-success bg-opacity-05 border border-success-subtle rounded-3">
                                    <x-user-avatar :user="$report->user" :size="28" class="flex-shrink-0" />
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-body text-truncate small">{{ $report->user?->name ?? 'Hệ thống' }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Báo cáo ngày {{ $report->date->format('d/m/Y') }} · {{ $report->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="d-flex flex-column align-items-center justify-content-center py-4 text-muted border border-dashed rounded-3" style="font-size: 13px;">
                                    <i class="fa-solid fa-file-xmark mb-2 fs-5 opacity-50"></i>
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
        <div class="card border border-light-subtle shadow-sm bg-secondary bg-opacity-05">
            <div class="card-body p-4">
                <h6 class="fw-bold text-body mb-3">Thao tác nhanh</h6>
                <div class="row g-2">
                    @if(auth()->user()->can(\App\Enums\Permission::QUOTATION_TRACKING_VIEW->value)
                        && auth()->user()->hasAnyRole([\App\Enums\Role::KINH_DOANH->value, \App\Enums\Role::TP_KINH_DOANH->value, \App\Enums\Role::GIAM_DOC->value]))
                    <div class="col-6">
                        <a href="{{ route('app.quotation-tracking.index') }}" class="btn btn-outline-primary w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7">
                            <i class="fa-solid fa-file-invoice-dollar"></i> Báo giá
                        </a>
                    </div>
                    @endif
                    @if(auth()->user()->can(\App\Enums\Permission::QUOTATION_TRACKING_CREATE->value)
                        && auth()->user()->hasAnyRole([\App\Enums\Role::KINH_DOANH->value, \App\Enums\Role::TP_KINH_DOANH->value]))
                    <div class="col-6">
                        <a href="{{ route('app.quotation-docs.index') }}" class="btn btn-primary w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7 text-white">
                            <i class="fa-solid fa-file-circle-plus"></i> Tạo báo giá
                        </a>
                    </div>
                    @endif
                    @can(\App\Enums\Permission::CUSTOMERS_VIEW->value)
                    <div class="col-6">
                        <a href="{{ route('app.customers.index') }}" class="btn btn-outline-primary w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7">
                            <i class="fa-solid fa-building-user"></i> Khách hàng
                        </a>
                    </div>
                    @endcan
                    @can(\App\Enums\Permission::REPORTS_SALES_VIEW->value)
                    <div class="col-6">
                        <a href="{{ route('app.reports.index') }}" class="btn btn-outline-success w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7">
                            <i class="fa-solid fa-chart-line"></i> Kinh doanh
                        </a>
                    </div>
                    @endcan
                    @can(\App\Enums\Permission::CASH_FLOW_VIEW->value)
                    <div class="col-6">
                        <a href="{{ route('app.finance.cash-flow') }}" class="btn btn-outline-success w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7">
                            <i class="fa-solid fa-money-bill-trend-up"></i> Dòng tiền
                        </a>
                    </div>
                    @endcan
                    @can(\App\Enums\Permission::INTERNAL_DOCS_VIEW->value)
                    <div class="col-6">
                        <a href="{{ route('app.internal-docs.index') }}" class="btn btn-outline-secondary w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7">
                            <i class="fa-solid fa-folder-open"></i> Công văn
                        </a>
                    </div>
                    @endcan
                    @can('hr-profiles.view')
                    <div class="col-6">
                        <a href="{{ route('app.hr.index') }}" class="btn btn-primary w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7 text-white">
                            <i class="fa-solid fa-users"></i> Nhân sự
                        </a>
                    </div>
                    @endcan
                    <div class="col-6">
                        <a href="{{ route('app.work-schedules.index') }}" class="btn btn-warning text-dark w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7">
                            <i class="fa-solid fa-calendar-days"></i> Lịch
                        </a>
                    </div>
                    @can('daily-reports.view')
                    <div class="col-6">
                        <a href="{{ route('app.daily-reports.index') }}" class="btn btn-success w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7 text-white">
                            <i class="fa-solid fa-chart-column"></i> Báo cáo
                        </a>
                    </div>
                    @endcan
                    @can('roles.view')
                    <div class="col-6">
                        <a href="{{ route('app.roles.index') }}" class="btn btn-danger w-100 py-3 px-2 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7 text-white">
                            <i class="fa-solid fa-shield-halved"></i> Quyền
                        </a>
                    </div>
                    @endcan
                    @can('settings.view')
                    <div class="col-12">
                        <a href="{{ route('app.settings.index') }}" class="btn btn-info text-dark w-100 py-3 px-3 fw-bold d-flex align-items-center justify-content-center gap-2 rounded-3 hover-lift fs-7">
                            <i class="fa-solid fa-gear"></i> Thiết lập hệ thống
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>

        @if(auth()->user()->hasAnyRole([\App\Enums\Role::IT->value, \App\Enums\Role::GIAM_DOC->value, \App\Enums\Role::HCNS->value]))
        <!-- Phân bổ vai trò -->
        <div class="card border border-light-subtle shadow-sm flex-grow-1 bg-secondary bg-opacity-05">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold text-body mb-0">Phân bổ vai trò</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 d-inline-flex align-items-center flex-shrink-0" style="font-size: 11px;">{{ $totalActiveUsersCount }} users</span>
                </div>
                <div class="d-flex flex-column gap-3">
                    @foreach($dashboardRoleDistribution as $r)
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 12px; font-weight: 600;">
                                <span class="text-body">{{ $r['name'] }}</span>
                                <span class="text-muted">{{ $r['count'] }} người</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--bs-secondary-bg, #e2e8f0); border-radius: 99px; overflow: hidden;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $totalActiveUsersCount > 0 ? ($r['count'] / $totalActiveUsersCount) * 100 : 0 }}%; border-radius: 99px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
