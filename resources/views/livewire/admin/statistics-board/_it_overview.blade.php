@if($isIT && $itStats)
    {{-- IT Tab Navigation --}}
    <div class="mb-4">
        <ul class="nav nav-pills gap-2 p-1 d-inline-flex bg-body-secondary border border-light-subtle rounded-pill">
            <li class="nav-item">
                <button wire:click="setTab('overview')" class="nav-link rounded-pill px-4 fw-semibold {{ $activeTab === 'overview' ? 'active' : 'text-muted' }}">Tổng quan</button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('security')" class="nav-link rounded-pill px-4 fw-semibold {{ $activeTab === 'security' ? 'active' : 'text-muted' }}">An ninh & Log</button>
            </li>
        </ul>
    </div>

    @if($activeTab === 'overview')
        {{-- ── IT DASHBOARD OVERVIEW ──────────────────────────────── --}}
        {{-- Row 1: User & Session Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 bg-primary bg-opacity-05 shadow-none h-100">
                    <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                        <div class="avatar bg-primary shadow-primary rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px !important; height: 44px !important; min-width: 44px !important;">
                            <i class="fi fi-rr-user"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Tổng người dùng</div>
                            <h3 class="mb-0 fw-bold text-body mt-1">{{ number_format($itStats['total_users']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 bg-success bg-opacity-05 shadow-none h-100">
                    <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                        <div class="avatar bg-success shadow-success rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px !important; height: 44px !important; min-width: 44px !important;">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Đang kích hoạt</div>
                            <h3 class="mb-0 fw-bold text-body mt-1">{{ number_format($itStats['active_users']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 bg-danger bg-opacity-05 shadow-none h-100">
                    <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                        <div class="avatar bg-danger shadow-danger rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px !important; height: 44px !important; min-width: 44px !important;">
                            <i class="fa-solid fa-user-lock"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Bị khóa</div>
                            <h3 class="mb-0 fw-bold text-body mt-1">{{ number_format($itStats['locked_users']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 bg-warning bg-opacity-05 shadow-none h-100">
                    <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                        <div class="avatar bg-warning shadow-warning rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px !important; height: 44px !important; min-width: 44px !important;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Phiên truy cập</div>
                            <h3 class="mb-0 fw-bold text-body mt-1">{{ number_format($itStats['system']['active_sessions']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: System Health & Control --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="mb-0 fw-bold text-muted text-uppercase small" style="font-size: 11px;">Tài nguyên ổ đĩa</h6>
                            <div class="fw-bold {{ $itStats['system']['disk_percent'] > 80 ? 'text-danger' : 'text-success' }}">
                                {{ $itStats['system']['disk_percent'] }}%
                            </div>
                        </div>
                        <div class="progress mb-2" style="height: 10px; background-color: var(--bs-secondary-bg, #e2e8f0); border-radius: 99px; overflow: hidden;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated {{ $itStats['system']['disk_percent'] > 80 ? 'bg-danger' : 'bg-primary' }}"
                                 role="progressbar" style="width: {{ $itStats['system']['disk_percent'] }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 12px;">
                            <span>Sử dụng: <b>{{ $itStats['system']['disk_used'] }} GB</b></span>
                            <span>Trống: <b>{{ $itStats['system']['disk_free'] }} GB</b></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="mb-3 fw-bold text-muted text-uppercase small" style="font-size: 11px;">Trung tâm điều hành</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <button wire:click="clearCache" wire:loading.attr="disabled" class="btn btn-sm btn-outline-primary fw-semibold d-flex align-items-center gap-1 px-3">
                                    <span wire:loading wire:target="clearCache" class="spinner-border spinner-border-sm me-1"></span>
                                    <i wire:loading.remove wire:target="clearCache" class="fa-solid fa-arrows-rotate"></i>
                                    Dọn dẹp Cache
                                </button>
                                <button wire:click="clearLogs" wire:loading.attr="disabled" class="btn btn-sm btn-outline-danger fw-semibold d-flex align-items-center gap-1 px-3">
                                    <span wire:loading wire:target="clearLogs" class="spinner-border spinner-border-sm me-1"></span>
                                    <i wire:loading.remove wire:target="clearLogs" class="fa-solid fa-trash-can"></i>
                                    Làm trống Log
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4 mt-2">
                            <div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fs-8 d-inline-flex align-items-center">DB SIZE</span>
                                <div class="fw-bold mt-1" style="font-size: 13px;">{{ number_format($itStats['system']['db_size_mb'], 1) }} MB</div>
                            </div>
                            <div>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1 fs-8 d-inline-flex align-items-center">QUEUE</span>
                                <div class="fw-bold mt-1" style="font-size: 13px;">{{ number_format($itStats['system']['pending_jobs']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
                    <div class="card-body p-3">
                        <h6 class="mb-3 fw-bold text-muted text-uppercase small" style="font-size: 11px;">Môi trường</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Laravel</span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle fw-bold d-inline-flex align-items-center">{{ $itStats['system']['laravel_version'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">PHP</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-bold d-inline-flex align-items-center">{{ $itStats['system']['php_version'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Activity Log --}}
        <div class="row g-4 mb-4">
            <div class="col-md-12">
                <div class="card border border-light-subtle shadow-sm h-100 overflow-hidden bg-secondary bg-opacity-05">
                    <div class="card-header py-3 px-4 bg-body-secondary border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold">Hoạt động hệ thống mới nhất</h6>
                        <a href="{{ route('app.activity-log') }}" class="btn btn-sm btn-link text-decoration-none fw-bold">Xem tất cả</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="text-nowrap table-light">
                                    <tr>
                                        <th class="ps-3">Người dùng</th>
                                        <th>Hành động</th>
                                        <th class="pe-3">Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($itStats['recent_activities'] as $activity)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 28px !important; height: 28px !important; min-width: 28px !important; font-size: 11px;">
                                                    {{ $activity->causer ? strtoupper(substr($activity->causer->name, 0, 1)) : 'S' }}
                                                </div>
                                                <div class="fw-semibold">{{ $activity->causer ? $activity->causer->name : 'Hệ thống' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-wrap">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-uppercase fs-9px px-2 py-0.5">{{ $activity->event }}</span>
                                                {{ $activity->description }}
                                            </div>
                                        </td>
                                        <td class="text-muted pe-3 small">
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
                <div class="card border-0 shadow-sm h-100 bg-danger bg-opacity-05">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-2 d-inline-flex">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            </div>
                            <h6 class="mb-0 fw-bold text-danger">Cảnh báo Đăng nhập sai</h6>
                        </div>
                        <h2 class="display-6 fw-bold text-danger mb-1">{{ number_format($itStats['system']['failed_logins_24h']) }}</h2>
                        <div class="text-muted small">Lần thử thất bại trong 24h qua</div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border border-light-subtle shadow-sm h-100 bg-secondary bg-opacity-05">
                    <div class="card-header py-3 px-4 bg-body-secondary border-bottom">
                        <h6 class="mb-0 fw-bold">Người dùng hoạt động tích cực (7 ngày qua)</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            @foreach($itStats['top_users'] as $stat)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between gap-2 p-2 bg-body-secondary border border-light-subtle rounded-2">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center wh-30 fs-11px" style="width: 30px; height: 30px; flex-shrink: 0;">
                                            {{ $stat->causer ? strtoupper(substr($stat->causer->name, 0, 1)) : '?' }}
                                        </div>
                                        <span class="fw-bold text-truncate text-body small">{{ $stat->causer ? $stat->causer->name : 'N/A' }}</span>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">{{ $stat->total }} thao tác</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border border-light-subtle shadow-sm overflow-hidden bg-secondary bg-opacity-05">
            <div class="card-header py-3 px-4 bg-body-secondary border-bottom">
                <h6 class="mb-0 fw-bold">Nhật ký truy cập chi tiết</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="text-nowrap table-light">
                            <tr>
                                <th class="ps-3">Thời gian</th>
                                <th>Người dùng</th>
                                <th>Sự kiện</th>
                                <th>Mô tả</th>
                                <th class="pe-3">IP / Thiết bị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itStats['recent_activities'] as $activity)
                            <tr>
                                <td class="ps-3 text-nowrap small text-muted">{{ $activity->created_at->format('d/m H:i:s') }}</td>
                                <td>
                                    <span class="fw-bold text-body">{{ $activity->causer ? $activity->causer->name : 'Hệ thống' }}</span>
                                </td>
                                <td>
                                    @php($eventClass = in_array($activity->event, ['login','logout']) ? 'success' : ($activity->event == 'failed_login' ? 'danger' : 'info'))
                                    <span class="badge bg-{{ $eventClass }} bg-opacity-10 text-{{ $eventClass }} border border-{{ $eventClass }}-subtle text-uppercase fs-9px px-2 py-0.5">{{ $activity->event }}</span>
                                </td>
                                <td>{{ $activity->description }}</td>
                                <td class="text-muted pe-3 small">
                                    <div class="d-flex flex-column" style="font-size: 11px;">
                                        <span><i class="fa-solid fa-location-dot me-1"></i>{{ $activity->getExtraProperty('ip') ?? 'N/A' }}</span>
                                        <span class="text-truncate mxw-150px" title="{{ $activity->getExtraProperty('user_agent') }}">{{ $activity->getExtraProperty('user_agent') ?? 'N/A' }}</span>
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
