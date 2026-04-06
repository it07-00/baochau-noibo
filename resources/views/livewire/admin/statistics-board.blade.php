<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Bảng thống kê</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">Hệ thống</li>
                    <li class="breadcrumb-item active">Bảng thống kê</li>
                </ol>
            </nav>
        </div>
        <div>
            <select wire:model.live="year" class="form-select form-select-sm" style="width:auto">
                @foreach($years as $y)
                    <option value="{{ $y }}">Năm {{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @php
        $isIT = auth()->user()->hasRole('it');
    @endphp

    @if($dailyReportReminder)
        <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center gap-3 py-3 px-4" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 12px; border-left: 4px solid #f59e0b !important;">
            <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                <i class="bi bi-clock-fill text-warning fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0 fw-bold text-dark">Bạn chưa gửi báo cáo ngày hôm nay</h6>
                <p class="mb-0 small text-muted">Vui lòng gửi báo cáo trước khi kết thúc ngày làm việc.</p>
            </div>
            <a href="{{ route('app.daily-reports.index') }}" class="btn btn-warning btn-sm px-3 fw-bold shadow-sm" style="border-radius: 8px;">
                <i class="bi bi-pencil-square me-1"></i> Gửi báo cáo
            </a>
        </div>
    @endif

    @if($isIT && $itStats)
        {{-- IT Tab Navigation --}}
        <div class="mb-4">
            <ul class="nav nav-pills gap-2 p-1 bg-white rounded shadow-sm d-inline-flex" style="border: 1px solid #edf2f7;">
                <li class="nav-item">
                    <button wire:click="setTab('overview')" class="nav-link {{ $activeTab === 'overview' ? 'active shadow-sm' : 'text-dark' }} px-4 fw-bold" style="font-size: 13px;">Tổng quan</button>
                </li>
                <li class="nav-item">
                    <button wire:click="setTab('security')" class="nav-link {{ $activeTab === 'security' ? 'active shadow-sm' : 'text-dark' }} px-4 fw-bold" style="font-size: 13px;">An ninh & Log</button>
                </li>
                <li class="nav-item">
                    <button wire:click="setTab('env')" class="nav-link {{ $activeTab === 'env' ? 'active shadow-sm' : 'text-dark' }} px-4 fw-bold" style="font-size: 13px;">Cấu hình (.env)</button>
                </li>
            </ul>
        </div>

        @if($activeTab === 'overview')
            {{-- ── IT DASHBOARD OVERVIEW ──────────────────────────────── --}}
            {{-- Row 1: User & Session Stats --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4361ee" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                            </div>
                            <div>
                                <div class="small fw-medium text-white-50 text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">Tổng người dùng</div>
                                <div class="fw-bold fs-4">{{ number_format($itStats['total_users']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <div>
                                <div class="small fw-medium text-white-50 text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">Đang kích hoạt</div>
                                <div class="fw-bold fs-4">{{ number_format($itStats['active_users']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                            <div>
                                <div class="small fw-medium text-white-50 text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">Bị khóa</div>
                                <div class="fw-bold fs-4">{{ number_format($itStats['locked_users']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-white h-100" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            </div>
                            <div>
                                <div class="small fw-medium text-white-50 text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">Phiên truy cập</div>
                                <div class="fw-bold fs-4">{{ number_format($itStats['system']['active_sessions']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: System Health & Control --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="small fw-bold text-muted text-uppercase">Tài nguyên ổ đĩa</div>
                                <div class="small fw-bold {{ $itStats['system']['disk_percent'] > 80 ? 'text-danger' : 'text-success' }}">
                                    {{ $itStats['system']['disk_percent'] }}%
                                </div>
                            </div>
                            <div class="progress mb-2" style="height: 8px; background-color: #f0f0f0; border-radius: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated {{ $itStats['system']['disk_percent'] > 80 ? 'bg-danger' : 'bg-primary' }}"
                                     role="progressbar" style="width: {{ $itStats['system']['disk_percent'] }}%; border-radius: 10px;"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Sử dụng: <b>{{ $itStats['system']['disk_used'] }} GB</b></span>
                                <span>Trống: <b>{{ $itStats['system']['disk_free'] }} GB</b></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="small fw-bold text-muted text-uppercase mb-3">Trung tâm điều hành</div>
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
                                        <div class="badge bg-soft-info text-info rounded-pill px-2" style="font-size: 10px;">DB SIZE</div>
                                        <div class="small fw-bold">{{ number_format($itStats['system']['db_size_mb'], 1) }} MB</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="badge bg-soft-warning text-warning rounded-pill px-2" style="font-size: 10px;">QUEUE</div>
                                        <div class="small fw-bold">{{ number_format($itStats['system']['pending_jobs']) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="small fw-bold text-muted text-uppercase mb-2">Môi trường</div>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">Laravel</span>
                                    <span class="badge bg-light text-dark border fw-bold">{{ $itStats['system']['laravel_version'] }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">PHP</span>
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
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold">Hoạt động hệ thống mới nhất</h6>
                            <a href="{{ route('app.activity-log') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-nowrap">
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
                                                    <div class="rounded-circle bg-soft-secondary d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;flex-shrink:0">
                                                        {{ $activity->causer ? strtoupper(substr($activity->causer->name, 0, 1)) : 'S' }}
                                                    </div>
                                                    <div class="small fw-semibold">{{ $activity->causer ? $activity->causer->name : 'Hệ thống' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small text-wrap">
                                                    <span class="badge bg-soft-info text-info text-uppercase" style="font-size: 10px">{{ $activity->event }}</span>
                                                    {{ $activity->description }}
                                                </div>
                                            </td>
                                            <td class="text-muted small">
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
                    <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-soft-danger p-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                </div>
                                <h6 class="mb-0 fw-bold">Cảnh báo Đăng nhập sai</h6>
                            </div>
                            <div class="display-5 fw-bold text-danger mb-1">{{ number_format($itStats['system']['failed_logins_24h']) }}</div>
                            <div class="small text-muted">Lần thử thất bại trong 24h qua</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold">Người dùng hoạt động tích cực (7 ngày qua)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($itStats['top_users'] as $stat)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:30px;height:30px;font-size:11px">
                                                {{ $stat->causer ? strtoupper(substr($stat->causer->name, 0, 1)) : '?' }}
                                            </div>
                                            <span class="small fw-bold">{{ $stat->causer ? $stat->causer->name : 'N/A' }}</span>
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

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Nhật ký truy cập chi tiết</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-nowrap">
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
                                    <td class="small text-nowrap">{{ $activity->created_at->format('d/m H:i:s') }}</td>
                                    <td>
                                        <span class="fw-bold small">{{ $activity->causer ? $activity->causer->name : 'Hệ thống' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-{{ in_array($activity->event, ['login','logout']) ? 'success text-success' : ($activity->event == 'failed_login' ? 'danger text-danger' : 'info text-info') }} text-uppercase" style="font-size: 10px">{{ $activity->event }}</span>
                                    </td>
                                    <td class="small">{{ $activity->description }}</td>
                                    <td class="small text-muted">
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

        @elseif($activeTab === 'env')
            {{-- ── IT DASHBOARD ENV MANAGER ──────────────────────────── --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold">Quản trị cấu hình (.env)</h6>
                        <small class="text-muted">Chỉnh sửa trực tiếp tệp môi trường của hệ thống</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button wire:click="loadEnv" class="btn btn-sm btn-outline-secondary">Làm mới</button>
                        <button wire:click="saveEnv" onclick="return confirm('Bạn có chắc chắn muốn lưu thay đổi? Hệ thống sẽ khởi động lại cấu hình.')" class="btn btn-sm btn-primary px-3">Lưu cấu hình</button>
                    </div>
                </div>
                <div class="card-body bg-light-subtle">
                    <div class="alert alert-warning border-0 shadow-sm small py-2 d-flex align-items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <b>Thận trọng:</b> Thay đổi sai thông số .env có thể làm ngừng hoạt động toàn bộ hệ thống.
                    </div>

                    <div class="row g-3">
                        @foreach($envData as $key => $value)
                        <div class="col-md-6 col-lg-4">
                            <div class="p-2 bg-white rounded border shadow-sm">
                                <label class="small fw-bold text-muted mb-1 d-block" style="font-size: 10px">{{ $key }}</label>
                                @if(Str::contains($key, ['PASSWORD', 'SECRET', 'KEY', 'TOKEN']))
                                    <input type="password" wire:model="envData.{{ $key }}" class="form-control form-control-sm border-0 bg-light" style="font-family: monospace" placeholder="********">
                                @else
                                    <input type="text" wire:model="envData.{{ $key }}" class="form-control form-control-sm border-0 bg-light" style="font-family: monospace">
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @else
        {{-- ── BUSINESS DASHBOARD VIEW ──────────────────────────────── --}}

        {{-- KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-soft-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        </div>
                        <div>
                            <div class="small text-muted">Tổng KH</div>
                            <div class="fw-bold fs-5 text-primary">{{ number_format($totalCustomers) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        </div>
                        <div>
                            <div class="small text-muted">Hợp đồng {{ $year }}</div>
                            <div class="fw-bold fs-5 text-success">{{ number_format($totalContracts) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @if($canSeeFinance)
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-soft-warning d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-warning" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                        <div>
                            <div class="small text-muted">Giá trị HĐ (Tỷ)</div>
                            <div class="fw-bold fs-5 text-warning">{{ number_format($totalContractValue/1000000000, 2) }} B</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-soft-info d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-info" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline></svg>
                        </div>
                        <div>
                            <div class="small text-muted">Doanh số (Tỷ)</div>
                            <div class="fw-bold fs-5 text-info">{{ number_format($totalSales/1000000000, 2) }} B</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Trends & Charts Row --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">Diễn biến theo tháng — Năm {{ $year }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tháng</th>
                                        <th class="text-center">Hợp đồng</th>
                                        @if($canSeeFinance)
                                        <th class="text-end">Doanh số (đ)</th>
                                        <th class="text-end">Thực thu (đ)</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthly as $m => $data)
                                    <tr>
                                        <td class="fw-semibold">Tháng {{ $m }}</td>
                                        <td class="text-center">{{ $data['contracts'] ?: '—' }}</td>
                                        @if($canSeeFinance)
                                        <td class="text-end small">{{ $data['sales'] > 0 ? number_format($data['sales']) : '—' }}</td>
                                        <td class="text-end small text-success fw-bold">{{ $data['revenue'] > 0 ? number_format($data['revenue']) : '—' }}</td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @if($canSeeConsulting)
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold">Dự án tư vấn theo loại</h6>
                            <div class="btn-group btn-group-sm">
                                <button wire:click="$set('chartMode','quarter')" class="btn {{ $chartMode === 'quarter' ? 'btn-primary' : 'btn-outline-secondary' }}">Quý</button>
                                <button wire:click="$set('chartMode','year')" class="btn {{ $chartMode === 'year' ? 'btn-primary' : 'btn-outline-secondary' }}">Năm</button>
                            </div>
                        </div>
                        <div class="card-body" x-data="{ render() { if(window.renderConsultingChart) window.renderConsultingChart(); } }" x-init="setTimeout(() => render(), 100)" @chart-updated.window="render()">
                            <div id="consultingChartConfig" class="d-none" data-chart-data='@json($consultingChartData)' data-chart-mode="{{ $chartMode }}" data-year="{{ $year }}" data-years='@json(array_reverse($years))'></div>
                            <canvas id="consultingChart" height="260" wire:ignore></canvas>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold">Hợp đồng theo loại — {{ $year }}</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Loại HĐ</th>
                                        <th class="text-center">Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byType as $label => $data)
                                    <tr>
                                        <td class="small">{{ $label }}</td>
                                        <td class="text-center font-monospace">{{ $data['count'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

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
                                    <td class="small">{{ $row['label'] }}</td>
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

    {{-- ── SHARED FOOTER / SCRIPTS ────────────────────────────────── --}}
    @if($canSeeConsulting && !$isIT)
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
        (function () {
            const colors = ['#4361ee','#3a0ca3','#7209b7','#f72585','#4cc9f0'];
            window.renderConsultingChart = function() {
                const configEl = document.getElementById('consultingChartConfig');
                if (!configEl) return;
                try {
                    const chartData = JSON.parse(configEl.dataset.chartData);
                    const chartMode = configEl.dataset.chartMode;
                    const el = document.getElementById('consultingChart');
                    if (!el) return;
                    if (el._chartInstance) el._chartInstance.destroy();
                    const labels = chartMode === 'quarter' ? ['Q1','Q2','Q3','Q4'] : JSON.parse(configEl.dataset.years).map(y => 'Năm ' + y);
                    const datasets = Object.entries(chartData).map(([label, data], i) => ({
                        label, data, backgroundColor: colors[i % colors.length] + 'cc', borderColor: colors[i % colors.length], borderWidth: 1, borderRadius: 4
                    }));
                    el._chartInstance = new Chart(el, {
                        type: 'bar',
                        data: { labels, datasets },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } }
                        }
                    });
                } catch (e) { console.error('Chart error:', e); }
            };
            document.addEventListener('livewire:update', () => setTimeout(() => window.renderConsultingChart(), 100));
        })();
        </script>
        @endpush
    @endif
</div>
