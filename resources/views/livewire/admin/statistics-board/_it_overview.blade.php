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
                                <svg wire:loading.remove wire:target="clearCache" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 2a2 2 0 0 0-2 2v5H4a2 2 0 0 0-2 2v2c0 1.1.9 2 2 2h5v5c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2v-5h5a2 2 0 0 0 2-2v-2a2 2 0 0 0 2-2h-5V4a2 2 0 0 0-2-2h-2z"></path></svg>
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
        <div class="row g-4 mb-4 it-security-summary">
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
                                                <span class="badge bg-soft-info text-info text-uppercase fs-10px" >{{ $activity->event }}</span>
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
                <div class="card border-0 shadow-sm border-start border-4 border-danger h-100 it-panel-card it-security-alert-card">
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
                <div class="card border-0 shadow-sm h-100 it-panel-card it-top-user-card">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold">Người dùng hoạt động tích cực (7 ngày qua)</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 it-top-user-list">
                            @foreach($itStats['top_users'] as $stat)
                            <div class="col-md-6">
                                <div class="it-top-user-item d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center wh-30 fs-11px" >
                                            {{ $stat->causer ? strtoupper(substr($stat->causer->name, 0, 1)) : '?' }}
                                        </div>
                                        <span class="it-top-user-name fw-bold">{{ $stat->causer ? $stat->causer->name : 'N/A' }}</span>
                                    </div>
                                    <span class="it-top-user-count badge border">{{ $stat->total }} thao tác</span>
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
                                    <span class="badge bg-soft-{{ in_array($activity->event, ['login','logout']) ? 'success text-success' : ($activity->event == 'failed_login' ? 'danger text-danger' : 'info text-info') }} text-uppercase fs-10px" >{{ $activity->event }}</span>
                                </td>
                                <td class="">{{ $activity->description }}</td>
                                <td class=" text-muted">
                                    <div class="d-flex flex-column">
                                        <span><i class="fa-solid fa-location-dot"></i> {{ $activity->getExtraProperty('ip') ?? 'N/A' }}</span>
                                        <span class="text-truncate mxw-150px"  title="{{ $activity->getExtraProperty('user_agent') }}">{{ $activity->getExtraProperty('user_agent') ?? 'N/A' }}</span>
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
@endif
