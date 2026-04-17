<div>
    @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
    <div class="page-header d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">Bảng thống kê</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">Hệ thống</li>
                    <li class="breadcrumb-item active">Bảng thống kê</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <select wire:model.live="month" class="form-select" style="width:auto; min-width: 170px; min-height: 42px;">
                <option value="">Cả năm</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">Tháng {{ $m }}</option>
                @endfor
            </select>
            <select wire:model.live="year" class="form-select" style="width:auto; min-width: 170px; min-height: 42px;">
                @foreach($years as $y)
                    <option value="{{ $y }}">Năm {{ $y }}</option>
                @endforeach
            </select>
            <input
                type="date"
                wire:model.live="contractDateFrom"
                class="form-control"
                style="width:auto; min-width: 195px; min-height: 42px;"
                title="Lọc hợp đồng từ ngày ký"
            >
            <input
                type="date"
                wire:model.live="contractDateTo"
                class="form-control"
                style="width:auto; min-width: 195px; min-height: 42px;"
                title="Lọc hợp đồng đến ngày ký"
            >
            <button
                type="button"
                wire:click="clearContractDateFilter"
                class="btn btn-outline-secondary px-3"
                style="min-height: 42px;"
                @disabled($contractDateFrom === '' && $contractDateTo === '')
            >
                Xóa ngày
            </button>
        </div>
    </div>
    @endunless

    @php
        $isIT = auth()->user()->hasRole('it');
    @endphp
    @if($dailyReportReminder && !auth()->user()->hasAnyRole(['tu-van', 'ky-thuat', 'kinh-doanh', 'tp-kinh-doanh']))
        <div class="daily-report-reminder-alert alert bg-warning-subtle border-0 shadow-sm mb-4 d-flex align-items-center gap-3 py-3 px-4" style="border-radius: 12px; border-left: 4px solid #f59e0b !important;">
            <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                <i class="bi bi-clock-fill text-warning fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0 fw-bold text-body">Bạn chưa gửi báo cáo ngày hôm nay</h6>
                <p class="mb-0  text-muted">Vui lòng gửi báo cáo trước khi kết thúc ngày làm việc.</p>
            </div>
            <a href="{{ route('app.daily-reports.index') }}" class="btn btn-warning btn-sm px-3 fw-bold shadow-sm" style="border-radius: 8px;">
                <i class="bi bi-pencil-square me-1"></i> Gửi báo cáo
            </a>
        </div>
    @endif

    @if($isIT && $itStats)
        {{-- IT Tab Navigation --}}
        <div class="mb-4">
            <ul class="nav nav-pills gap-2 p-1 d-inline-flex it-dashboard-tabs">
                <li class="nav-item">
                    <button wire:click="setTab('overview')" class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}">Tổng quan</button>
                </li>
                <li class="nav-item">
                    <button wire:click="setTab('security')" class="nav-link {{ $activeTab === 'security' ? 'active' : '' }}">An ninh & Log</button>
                </li>
            </ul>
        </div>

        @if($activeTab === 'overview')
            {{-- ── IT DASHBOARD OVERVIEW ──────────────────────────────── --}}
            {{-- Row 1: User & Session Stats --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 text-white h-100 it-stat-card it-stat-primary">
                        <div class="card-body it-stat-body">
                            <div class="it-stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                            </div>
                            <div>
                                <div class="it-stat-label">Tổng người dùng</div>
                                <div class="it-stat-value">{{ number_format($itStats['total_users']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white h-100 it-stat-card it-stat-success">
                        <div class="card-body it-stat-body">
                            <div class="it-stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <div>
                                <div class="it-stat-label">Đang kích hoạt</div>
                                <div class="it-stat-value">{{ number_format($itStats['active_users']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white h-100 it-stat-card it-stat-danger">
                        <div class="card-body it-stat-body">
                            <div class="it-stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                            <div>
                                <div class="it-stat-label">Bị khóa</div>
                                <div class="it-stat-value">{{ number_format($itStats['locked_users']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 text-white h-100 it-stat-card it-stat-warning">
                        <div class="card-body it-stat-body">
                            <div class="it-stat-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <div>
                                <div class="it-stat-label">Phiên truy cập</div>
                                <div class="it-stat-value">{{ number_format($itStats['system']['active_sessions']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: System Health & Control --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 h-100 it-panel-card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="it-panel-title">Tài nguyên ổ đĩa</div>
                                <div class=" fw-bold {{ $itStats['system']['disk_percent'] > 80 ? 'text-danger' : 'text-success' }}">
                                    {{ $itStats['system']['disk_percent'] }}%
                                </div>
                            </div>
                            <div class="progress it-disk-progress mb-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated {{ $itStats['system']['disk_percent'] > 80 ? 'bg-danger' : 'bg-primary' }}"
                                     role="progressbar" style="width: {{ $itStats['system']['disk_percent'] }}%;"></div>
                            </div>
                            <div class="d-flex justify-content-between  text-muted">
                                <span>Sử dụng: <b>{{ $itStats['system']['disk_used'] }} GB</b></span>
                                <span>Trống: <b>{{ $itStats['system']['disk_free'] }} GB</b></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card border-0 h-100 it-panel-card">
                        <div class="card-body p-3">
                            <div class="it-panel-title mb-3">Trung tâm điều hành</div>
                            <div class="d-flex flex-wrap gap-2">
                                <button wire:click="clearCache" wire:loading.attr="disabled" class="btn btn-sm btn-outline-primary fw-semibold d-flex align-items-center gap-1">
                                    <span wire:loading wire:target="clearCache" class="spinner-border spinner-border-sm me-1"></span>
                                    <svg wire:loading.remove wire:target="clearCache" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 2a2 2 0 0 0-2 2v5H4a2 2 0 0 0-2 2v2c0 1.1.9 2 2 2h5v5c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2v-5h5a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-5V4a2 2 0 0 0-2-2h-2z"></path></svg>
                                    Dọn dẹp Cache
                                </button>
                                <button wire:click="clearLogs" wire:loading.attr="disabled" class="btn btn-sm btn-outline-danger fw-semibold d-flex align-items-center gap-1">
                                    <span wire:loading wire:target="clearLogs" class="spinner-border spinner-border-sm me-1"></span>
                                    <svg wire:loading.remove wire:target="clearLogs" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Làm trống Log
                                </button>
                                <div class="d-flex align-items-center gap-3 ms-2">
                                    <div class="text-center">
                                        <div class="badge bg-soft-info text-info it-panel-badge">DB SIZE</div>
                                        <div class=" fw-bold">{{ number_format($itStats['system']['db_size_mb'], 1) }} MB</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="badge bg-soft-warning text-warning it-panel-badge">QUEUE</div>
                                        <div class=" fw-bold">{{ number_format($itStats['system']['pending_jobs']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 h-100 it-panel-card">
                        <div class="card-body p-3">
                            <div class="it-panel-title mb-2">Môi trường</div>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class=" text-muted">Laravel</span>
                                    <span class="badge bg-light text-dark border fw-bold">{{ $itStats['system']['laravel_version'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class=" text-muted">PHP</span>
                                    <span class="badge bg-light text-dark border fw-bold">{{ $itStats['system']['php_version'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 3: Activity Log --}}
            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm h-100 it-activity-card">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold">Hoạt động hệ thống mới nhất</h6>
                            <a href="{{ route('app.activity-log') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 it-activity-table">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>Người dùng</th>
                                            <th>Hành động</th>
                                            <th>Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($itStats['recent_activities'] as $activity)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="it-user-badge">
                                                        {{ $activity->causer ? strtoupper(substr($activity->causer->name, 0, 1)) : 'S' }}
                                                    </div>
                                                    <div class=" fw-semibold">{{ $activity->causer ? $activity->causer->name : 'Hệ thống' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class=" text-wrap">
                                                    <span class="badge bg-soft-info text-info text-uppercase" style="font-size: 10px">{{ $activity->event }}</span>
                                                    {{ $activity->description }}
                                                </div>
                                            </td>
                                            <td class="text-muted ">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">Chưa có hoạt động nào</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'security')
            {{-- ── IT DASHBOARD SECURITY ──────────────────────────────── --}}
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm border-start border-4 border-danger h-100 it-panel-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-soft-danger p-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                </div>
                                <h6 class="mb-0 fw-bold">Cảnh báo Đăng nhập sai</h6>
                            </div>
                            <div class="display-5 fw-bold text-danger mb-1">{{ number_format($itStats['system']['failed_logins_24h']) }}</div>
                            <div class=" text-muted">Lần thử thất bại trong 24h qua</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm h-100 it-panel-card">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-bold">Người dùng hoạt động tích cực (7 ngày qua)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($itStats['top_users'] as $stat)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded border">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:30px;height:30px;font-size:11px">
                                                {{ $stat->causer ? strtoupper(substr($stat->causer->name, 0, 1)) : '?' }}
                                            </div>
                                            <span class=" fw-bold">{{ $stat->causer ? $stat->causer->name : 'N/A' }}</span>
                                        </div>
                                        <span class="badge bg-white text-dark border">{{ $stat->total }} thao tác</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm it-activity-card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">Nhật ký truy cập chi tiết</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 it-activity-table">
                            <thead class="text-nowrap">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Người dùng</th>
                                    <th>Sự kiện</th>
                                    <th>Mô tả</th>
                                    <th>IP / Thiết bị</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itStats['recent_activities'] as $activity)
                                <tr>
                                    <td class=" text-nowrap">{{ $activity->created_at->format('d/m H:i:s') }}</td>
                                    <td>
                                        <span class="fw-bold ">{{ $activity->causer ? $activity->causer->name : 'Hệ thống' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-{{ in_array($activity->event, ['login','logout']) ? 'success text-success' : ($activity->event == 'failed_login' ? 'danger text-danger' : 'info text-info') }} text-uppercase" style="font-size: 10px">{{ $activity->event }}</span>
                                    </td>
                                    <td class="">{{ $activity->description }}</td>
                                    <td class=" text-muted">
                                        <div class="d-flex flex-column">
                                            <span><i class="bi bi-geo-alt"></i> {{ $activity->getExtraProperty('ip') ?? 'N/A' }}</span>
                                            <span class="text-truncate" style="max-width: 150px" title="{{ $activity->getExtraProperty('user_agent') }}">{{ $activity->getExtraProperty('user_agent') ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @endif
    @else
        {{-- ── BUSINESS DASHBOARD VIEW ──────────────────────────────── --}}

        {{-- KPI Cards --}}
        @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card kpi-modern-card h-100">
                    <div class="card-body p-3 p-md-4 d-flex flex-column gap-2">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="kpi-modern-title">
                                Tổng KH
                                @if($month !== '')
                                    - Tháng {{ $month }}/{{ $year }}
                                @else
                                    - Năm {{ $year }}
                                @endif
                            </div>
                            <div class="kpi-modern-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                            </div>
                        </div>
                        <div class="kpi-modern-value">{{ number_format($totalCustomers) }}</div>
                        <div class="kpi-modern-sparkline" aria-hidden="true">
                            <svg viewBox="0 0 180 36" preserveAspectRatio="none">
                                <path d="M2 28 C10 30, 14 14, 24 16 C32 17, 36 28, 46 26 C57 24, 62 10, 72 12 C83 14, 89 30, 98 28 C108 26, 112 16, 122 17 C132 18, 137 31, 147 26 C156 22, 162 14, 178 18"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card kpi-modern-card kpi-contracts h-100">
                    <div class="card-body p-3 p-md-4 d-flex flex-column gap-2">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="kpi-modern-title">
                                Hợp đồng
                                @if($month !== '')
                                    - Tháng {{ $month }}/{{ $year }}
                                @else
                                    - Năm {{ $year }}
                                @endif
                            </div>
                            <div class="kpi-modern-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                        </div>
                        <div class="kpi-modern-value">{{ number_format($totalContracts) }}</div>
                        <div class="kpi-modern-sparkline" aria-hidden="true">
                            <svg viewBox="0 0 180 36" preserveAspectRatio="none">
                                <path d="M2 27 C11 24, 16 30, 26 27 C35 24, 40 14, 50 16 C60 18, 64 31, 74 29 C84 27, 89 19, 98 21 C108 23, 114 31, 124 27 C134 23, 138 12, 148 14 C158 17, 164 27, 178 23"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            @if($canSeeFinance)
            <div class="col-md-3 col-6">
                <div class="card kpi-modern-card kpi-value h-100">
                    <div class="card-body p-3 p-md-4 d-flex flex-column gap-2">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="kpi-modern-title">
                                Giá trị HĐ (Triệu)
                                @if($month !== '')
                                    - Tháng {{ $month }}/{{ $year }}
                                @endif
                            </div>
                            <div class="kpi-modern-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            </div>
                        </div>
                        <div class="kpi-modern-value">{{ number_format($totalContractValue/1000000, 2) }} Tr</div>
                        <div class="kpi-modern-sparkline" aria-hidden="true">
                            <svg viewBox="0 0 180 36" preserveAspectRatio="none">
                                <path d="M2 29 C12 27, 18 22, 28 24 C38 27, 42 33, 52 30 C62 26, 67 17, 78 18 C88 19, 92 28, 102 27 C112 26, 117 14, 128 15 C139 16, 144 25, 154 24 C163 23, 168 18, 178 20"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card kpi-modern-card kpi-sales h-100">
                    <div class="card-body p-3 p-md-4 d-flex flex-column gap-2">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="kpi-modern-title">
                                Doanh số ghi nhận (Triệu)
                                @if($month !== '')
                                    - Tháng {{ $month }}/{{ $year }}
                                @endif
                            </div>
                            <div class="kpi-modern-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline></svg>
                            </div>
                        </div>
                        <div class="kpi-modern-value">{{ number_format($totalSales/1000000, 2) }} Tr</div>
                        <div class="kpi-modern-sparkline" aria-hidden="true">
                            <svg viewBox="0 0 180 36" preserveAspectRatio="none">
                                <path d="M2 30 C11 33, 15 19, 25 20 C34 20, 39 27, 49 26 C59 25, 64 13, 74 14 C84 14, 89 22, 99 24 C109 26, 113 15, 123 16 C133 17, 138 27, 148 27 C158 27, 163 15, 178 12"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endunless

        {{-- Trends & Charts Row --}}
        @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Tổng quan vận hành theo tháng</h6>
                            <small class="text-muted">Biến động hợp đồng, doanh số và thực thu trong năm {{ $year }}</small>
                        </div>
                        <span class="badge bg-light text-dark border">Năm {{ $year }}</span>
                    </div>
                    <div class="card-body p-3" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                        <div id="monthlyOverviewConfig" class="d-none" data-monthly='@json($monthly)' data-can-see-finance="{{ $canSeeFinance ? 1 : 0 }}"></div>
                        <canvas id="monthlyOverviewChart" style="height:220px;" wire:ignore></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Cơ cấu hợp đồng theo dịch vụ</h6>
                            <small class="text-muted">Phân bổ theo 6 nhóm hợp đồng của công ty</small>
                        </div>
                        <span class="badge bg-light text-dark border">Năm {{ $year }}</span>
                    </div>
                    <div class="card-body p-3" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                        <div id="workloadChartConfig" class="d-none" data-by-type='@json($byType)'></div>
                        <canvas id="teamWorkloadChart" style="height:220px;" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endunless

        @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">TỈ LỆ DOANH SỐ THEO NGUỒN THÔNG TIN</h6>
                            <small class="text-muted">Dựa trên doanh số ghi nhận của tất cả loại hợp đồng</small>
                        </div>
                    </div>
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                        <div id="sourceSalesConfig" class="d-none" data-chart='@json($sourceSalesChart)'></div>
                        <canvas id="sourceSalesChart" style="height:220px;" wire:ignore></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Dịch vụ: báo giá vs ký hợp đồng</h6>
                            <small class="text-muted">Tháng {{ $insightMonth }}/{{ $year }}</small>
                        </div>
                    </div>
                    <div class="card-body p-3" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                        <div id="serviceInsightConfig" class="d-none" data-chart='@json($serviceInsightChart)'></div>
                        <canvas id="serviceInsightChart" style="height:220px;" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-bold">Khu vực: báo giá, ký hợp đồng, doanh số</h6>
                            <small class="text-muted">Tháng {{ $insightMonth }}/{{ $year }}</small>
                        </div>
                    </div>
                    <div class="card-body p-3" x-data="{ render() { if(window.renderStatisticsBoardCharts) window.renderStatisticsBoardCharts(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                        <div id="regionInsightConfig" class="d-none" data-chart='@json($regionInsightChart)'></div>
                        <canvas id="regionInsightChart" style="height:220px;" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endunless

        @if($canSeeTechnical)
        {{-- Technical Dept Row --}}
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">Bộ phận Kỹ thuật — Năm {{ $year }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Loại HĐ</th>
                                    <th class="text-center">Số HĐ</th>
                                    <th class="text-center">Hoàn thành</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($technicalStats as $row)
                                <tr>
                                    <td class="">{{ $row['label'] }}</td>
                                    <td class="text-center fw-bold">{{ $row['count'] }}</td>
                                    <td class="text-center text-success">{{ $row['completed'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        const palette = ['#4f7cff', '#2ec27e', '#f5a524', '#f05252', '#7c3aed', '#0ea5e9', '#06b6d4', '#f97316'];

        function compactCurrency(value) {
            if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'B';
            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
            if (value >= 1000) return (value / 1000).toFixed(1) + 'K';
            return value;
        }

        function destroyChart(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (canvas && canvas._chartInstance) {
                canvas._chartInstance.destroy();
                canvas._chartInstance = null;
            }
        }

        function renderMonthlyOverviewChart() {
            const configEl = document.getElementById('monthlyOverviewConfig');
            const canvas = document.getElementById('monthlyOverviewChart');
            if (!configEl || !canvas) return;
            destroyChart('monthlyOverviewChart');

            const monthly = JSON.parse(configEl.dataset.monthly || '{}');
            const canSeeFinance = String(configEl.dataset.canSeeFinance) === '1';
            const labels = Object.keys(monthly).map(m => 'Tháng ' + m);
            const contractData = Object.values(monthly).map(item => Number(item.contracts || 0));
            const salesData = Object.values(monthly).map(item => Number(item.sales || 0));
            const revenueData = Object.values(monthly).map(item => Number(item.revenue || 0));

            const datasets = [{
                label: 'Hợp đồng ký mới', type: 'bar', data: contractData,
                backgroundColor: 'rgba(79,124,255,0.75)', borderRadius: 4, yAxisID: 'y'
            }];
            const scales = {
                y: { title: { display: true, text: 'Số HĐ' }, ticks: { stepSize: 1 } }
            };

            if (canSeeFinance) {
                datasets.push({ label: 'Doanh số ghi nhận', type: 'bar', data: salesData, backgroundColor: 'rgba(240,82,82,0.7)', borderRadius: 4, yAxisID: 'y1' });
                datasets.push({ label: 'Thực thu', type: 'line', data: revenueData, borderColor: '#2ec27e', backgroundColor: 'rgba(46,194,126,0.12)', fill: false, tension: 0.4, pointRadius: 4, yAxisID: 'y1' });
                scales.y1 = { position: 'right', title: { display: true, text: 'Giá trị (VND)' }, grid: { drawOnChartArea: false }, ticks: { callback: v => compactCurrency(v) } };
            }

            canvas._chartInstance = new Chart(canvas, {
                type: 'bar',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top' } },
                    scales
                }
            });
        }

        function renderWorkloadChart() {
            const configEl = document.getElementById('workloadChartConfig');
            const canvas = document.getElementById('teamWorkloadChart');
            if (!configEl || !canvas) return;
            destroyChart('teamWorkloadChart');

            const byType = JSON.parse(configEl.dataset.byType || '{}');
            const entries = Object.entries(byType);
            if (!entries.length) return;

            canvas._chartInstance = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: entries.map(([label]) => label),
                    datasets: [{ data: entries.map(([, item]) => Number(item.count || 0)), backgroundColor: palette }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
            });
        }

        function renderSourceSalesChart() {
            const configEl = document.getElementById('sourceSalesConfig');
            const canvas = document.getElementById('sourceSalesChart');
            if (!configEl || !canvas) return;
            destroyChart('sourceSalesChart');

            const payload = JSON.parse(configEl.dataset.chart || '{}');
            const labels = payload.labels || [];
            const data = payload.datasets || [];

            const colorMap = {
                'SALE': '#007bff', 'KHAI THÁC': '#c084fc', 'TÁI KÝ': '#facc15',
                'MARKETING': '#b91c1c', 'CHUYỂN THÔNG TIN': '#15803d',
                'CÔNG TY': '#f43f5e', 'MỚI': '#0ea5e9'
            };
            const fallback = ['#007bff','#c084fc','#facc15','#b91c1c','#15803d','#f43f5e','#0ea5e9','#6366f1','#f97316','#10b981'];
            const colors = labels.map((l, i) => colorMap[l.toUpperCase()] || fallback[i % fallback.length]);

            canvas._chartInstance = new Chart(canvas, {
                type: 'pie',
                data: { labels, datasets: [{ data, backgroundColor: colors }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { font: { weight: '600' } } },
                        tooltip: { callbacks: { label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            return ctx.label + ': ' + compactCurrency(ctx.raw) + ' (' + ((ctx.raw / total) * 100).toFixed(1) + '%)';
                        }}}
                    }
                }
            });
        }

        function renderServiceInsightChart() {
            const configEl = document.getElementById('serviceInsightConfig');
            const canvas = document.getElementById('serviceInsightChart');
            if (!configEl || !canvas) return;
            destroyChart('serviceInsightChart');

            const payload = JSON.parse(configEl.dataset.chart || '{}');
            const labels = payload.labels || [];

            canvas._chartInstance = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Đã báo giá', data: payload.quoted || [], backgroundColor: 'rgba(245,158,11,0.75)', borderRadius: 4 },
                        { label: 'Đã ký hợp đồng', data: payload.signed || [], backgroundColor: 'rgba(37,99,235,0.75)', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { title: { display: true, text: 'Số lượng hồ sơ' }, ticks: { stepSize: 1 } } }
                }
            });
        }

        function renderRegionInsightChart() {
            const configEl = document.getElementById('regionInsightConfig');
            const canvas = document.getElementById('regionInsightChart');
            if (!configEl || !canvas) return;
            destroyChart('regionInsightChart');

            const payload = JSON.parse(configEl.dataset.chart || '{}');
            const labels = payload.labels || [];

            canvas._chartInstance = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Báo giá', type: 'bar', data: payload.quoted || [], backgroundColor: 'rgba(245,158,11,0.75)', borderRadius: 4, yAxisID: 'y' },
                        { label: 'Ký hợp đồng', type: 'bar', data: payload.signed || [], backgroundColor: 'rgba(37,99,235,0.75)', borderRadius: 4, yAxisID: 'y' },
                        { label: 'Doanh số (HĐ)', type: 'line', data: payload.revenue || [], borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.12)', fill: false, tension: 0.4, pointRadius: 4, yAxisID: 'y1' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: { title: { display: true, text: 'Số lượng hồ sơ' }, ticks: { stepSize: 1 } },
                        y1: { position: 'right', title: { display: true, text: 'VND' }, grid: { drawOnChartArea: false }, ticks: { callback: v => compactCurrency(v) } }
                    }
                }
            });
        }

        window.renderStatisticsBoardCharts = function() {
            renderMonthlyOverviewChart();
            renderWorkloadChart();
            renderSourceSalesChart();
            renderServiceInsightChart();
            renderRegionInsightChart();
        };

        document.addEventListener('livewire:update', function () {
            setTimeout(function() { window.renderStatisticsBoardCharts(); }, 100);
        });

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function() { window.renderStatisticsBoardCharts(); }, 100);
        });
    })();
    </script>
    @endpush
</div>