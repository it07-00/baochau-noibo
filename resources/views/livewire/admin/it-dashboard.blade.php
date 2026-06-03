<div>
    {{-- Tab Nav --}}
    <div class="mb-4">
        <ul class="nav nav-pills gap-2 p-1 d-inline-flex it-dashboard-tabs">
            <li class="nav-item">
                <button wire:click="setTab('overview')" class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}">Tổng quan</button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('logs')" class="nav-link {{ $activeTab === 'logs' ? 'active' : '' }}">
                    Log lỗi
                    @if(count($recentErrors) > 0)
                        <span class="badge bg-danger ms-1">{{ count($recentErrors) }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button wire:click="setTab('sessions')" class="nav-link {{ $activeTab === 'sessions' ? 'active' : '' }}">Phiên & Truy cập</button>
            </li>            <li class="nav-item">
                <button wire:click="setTab('backup')" class="nav-link {{ $activeTab === 'backup' ? 'active' : '' }}">Backup DB</button>
            </li>        </ul>
    </div>

    {{-- ═══ TAB: TỔNG QUAN ═══ --}}
    @if($activeTab === 'overview')

        {{-- Row 1: KPI stat cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 text-white h-100 it-stat-card it-stat-primary">
                    <div class="card-body it-stat-body">
                        <div class="it-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                        </div>
                        <div>
                            <div class="it-stat-label">Người dùng</div>
                            <div class="it-stat-value">{{ $totalUsers }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 text-white h-100 it-stat-card it-stat-success">
                    <div class="card-body it-stat-body">
                        <div class="it-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div>
                            <div class="it-stat-label">Đang kích hoạt</div>
                            <div class="it-stat-value">{{ $activeUsers }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 text-white h-100 it-stat-card it-stat-warning">
                    <div class="card-body it-stat-body">
                        <div class="it-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                        <div>
                            <div class="it-stat-label">Phiên truy cập</div>
                            <div class="it-stat-value">{{ $activeSessions }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 text-white h-100 it-stat-card {{ $failedLogins24h > 0 ? 'it-stat-danger' : 'it-stat-primary' }}">
                    <div class="card-body it-stat-body">
                        <div class="it-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <div>
                            <div class="it-stat-label">Đăng nhập sai (24h)</div>
                            <div class="it-stat-value">{{ $failedLogins24h }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Disk + Controls + Env --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 h-100 it-panel-card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="it-panel-title">Tài nguyên ổ đĩa</div>
                            <span class="fw-bold {{ $disk['diskPercent'] >= 90 ? 'text-danger' : ($disk['diskPercent'] >= 70 ? 'text-warning' : 'text-success') }}">
                                {{ $disk['diskPercent'] }}%
                            </span>
                        </div>
                        <div class="it-disk-progress mb-2">
                            <div class="progress-bar {{ $disk['diskPercent'] >= 90 ? 'bg-danger' : ($disk['diskPercent'] >= 70 ? 'bg-warning' : 'bg-primary') }} progress-bar-striped progress-bar-animated"
                                 style="width:{{ $disk['diskPercent'] }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted fs-12px" >
                            <span>Dùng: <b>{{ number_format($disk['diskUsed'] / (1024**3), 1) }} GB</b></span>
                            <span>Trống: <b>{{ number_format($disk['diskFree'] / (1024**3), 1) }} GB</b></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card border-0 h-100 it-panel-card">
                    <div class="card-body p-3">
                        <div class="it-panel-title mb-3">Trung tâm điều hành</div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button wire:click="clearCache" wire:loading.attr="disabled" class="btn btn-sm btn-outline-primary fw-semibold d-flex align-items-center gap-1">
                                <span wire:loading wire:target="clearCache" class="spinner-border spinner-border-sm"></span>
                                <svg wire:loading.remove wire:target="clearCache" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 .49-3.5"></path></svg>
                                Dọn cache
                            </button>
                            <button wire:click="clearLogs" wire:loading.attr="disabled" class="btn btn-sm btn-outline-danger fw-semibold d-flex align-items-center gap-1">
                                <span wire:loading wire:target="clearLogs" class="spinner-border spinner-border-sm"></span>
                                <svg wire:loading.remove wire:target="clearLogs" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6M14 11v6M9 6V4h6v2"></path></svg>
                                Làm trống log
                            </button>
                            <div class="ms-auto d-flex gap-3">
                                <div class="text-center">
                                    <div class="badge bg-soft-info text-info it-panel-badge">DB SIZE</div>
                                    <div class="fw-bold contract-text-13px" >{{ number_format($dbSize, 1) }} MB</div>
                                </div>
                                <div class="text-center">
                                    <div class="badge {{ $failedJobs > 0 ? 'bg-soft-danger text-danger' : 'bg-soft-warning text-warning' }} it-panel-badge">QUEUE</div>
                                    <div class="fw-bold {{ $failedJobs > 0 ? 'text-danger' : '' }} contract-text-13px" >
                                        {{ $pendingJobs }}{{ $failedJobs > 0 ? ' / '.$failedJobs.' lỗi' : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 h-100 it-panel-card">
                    <div class="card-body p-3">
                        <div class="it-panel-title mb-3">Môi trường</div>
                        <div class="d-flex flex-column gap-2 contract-text-13px" >
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Laravel</span>
                                <span class="badge bg-light text-dark border fw-bold">{{ $laravelVersion }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">PHP</span>
                                <span class="badge bg-light text-dark border fw-bold">{{ $phpVersion }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Log file</span>
                                <span class="badge {{ $logSizeKb > 5000 ? 'bg-soft-danger text-danger' : 'bg-light text-dark border' }} fw-bold">{{ $logSizeKb }} KB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Phân bổ role + Top hoạt động --}}
        <div class="row g-3 mb-4">
            <div class="col-md-5">
                <div class="card border-0 h-100 it-panel-card">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between bg-gradient-white border-bottom" >
                        <h6 class="mb-0 fw-bold">Người dùng hệ thống</h6>
                        <div class="d-flex gap-3 text-center fs-12px" >
                            <div><div class="fw-bold text-success fs-6">{{ $activeUsers }}</div><div class="text-muted">Hoạt động</div></div>
                            <div><div class="fw-bold text-danger fs-6">{{ $lockedUsers }}</div><div class="text-muted">Bị khóa</div></div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @foreach($roleDistribution as $r)
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom contract-text-13px" >
                                <div class="d-flex align-items-center gap-2">
                                    <div class="it-user-badge wh-24 fs-10px" >{{ strtoupper(substr($r['name'], 0, 1)) }}</div>
                                    <span>{{ $r['name'] }}</span>
                                </div>
                                <span class="badge bg-light text-dark border fw-bold">{{ $r['count'] }} người</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card border-0 h-100 it-activity-card">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold">Top hoạt động (7 ngày)</h6>
                        <span class="badge bg-soft-info text-info it-panel-badge">{{ number_format($totalActivities7d) }} lượt</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0 it-activity-table">
                            <thead class="text-nowrap">
                                <tr>
                                    <th>#</th>
                                    <th>Người dùng</th>
                                    <th class="text-end">Lượt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topUsers as $i => $act)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="it-user-badge">{{ $act->causer ? strtoupper(substr($act->causer->name, 0, 1)) : '?' }}</div>
                                                <span class="fw-semibold">{{ $act->causer?->name ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($act->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: Activity log gần nhất --}}
        <div class="card border-0 it-activity-card">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">Nhật ký hoạt động gần nhất</h6>
                <a href="{{ route('app.activity-log') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 it-activity-table text-nowrap">
                        <thead>
                            <tr>
                                <th>Người dùng</th>
                                <th>Hành động</th>
                                <th>Đối tượng</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities as $act)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="it-user-badge">{{ $act->causer ? strtoupper(substr($act->causer->name, 0, 1)) : 'S' }}</div>
                                            <span class="fw-semibold">{{ $act->causer?->name ?? 'Hệ thống' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info text-uppercase fs-10px" >{{ $act->event }}</span>
                                        {{ $act->description }}
                                    </td>
                                    <td class="text-muted">{{ class_basename($act->subject_type ?? '') ?: '—' }}</td>
                                    <td class="text-muted">{{ $act->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Chưa có nhật ký.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endif

    {{-- ═══ TAB: LOG LỖI ═══ --}}
    @if($activeTab === 'logs')
        <div class="card border-0 it-activity-card">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold">Log lỗi hệ thống</h6>
                    <small class="text-muted">laravel.log — {{ $logSizeKb }} KB</small>
                </div>
                <button wire:click="clearLogs" wire:loading.attr="disabled" class="btn btn-sm btn-outline-danger fw-semibold d-flex align-items-center gap-1">
                    <span wire:loading wire:target="clearLogs" class="spinner-border spinner-border-sm"></span>
                    Làm trống log
                </button>
            </div>
            <div class="card-body p-0">
                @if(count($recentErrors) === 0)
                    <div class="text-center text-success py-5">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3 d-block mx-auto opacity-75"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <div class="fw-semibold">Không có lỗi nào trong log</div>
                        <small class="text-muted">Hệ thống đang hoạt động ổn định</small>
                    </div>
                @else
                    <div style="max-height:520px; overflow-y:auto; background:#0f172a; border-radius:0 0 16px 16px;">
                        @foreach($recentErrors as $line)
                            <div class="px-4 py-2 border-bottom" style="font-size:0.73rem; font-family:'Consolas','Courier New',monospace; color:#fca5a5; white-space:pre-wrap; word-break:break-all; border-color:#1e293b !important;">{{ $line }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ═══ TAB: PHIÊN & TRUY CẬP ═══ --}}
    @if($activeTab === 'sessions')

        {{-- Sessions theo giờ --}}
        <div class="card border-0 it-panel-card mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between bg-gradient-white border-bottom" >
                <h6 class="mb-0 fw-bold">Phiên hoạt động theo giờ (24h gần nhất)</h6>
                <span class="badge bg-primary">{{ $activeSessions }} sessions</span>
            </div>
            <div class="card-body">
                @if(count($sessionsByHour) > 0)
                    <div class="d-flex align-items-end gap-1 h-110px" >
                        @for($h = 0; $h <= 23; $h++)
                            <div class="flex-fill d-flex flex-column align-items-center justify-content-end h-100" >
                                <div class="rounded-top w-100 {{ $this->sessionHourMeta($sessionsByHour, $h, $this->maxSessionCount($sessionsByHour))['is_now'] ? 'bg-success' : 'bg-primary' }}"
                                     style="height:{{ $this->sessionHourMeta($sessionsByHour, $h, $this->maxSessionCount($sessionsByHour))['height_pct'] }}%; min-height:5px; opacity:{{ $this->sessionHourMeta($sessionsByHour, $h, $this->maxSessionCount($sessionsByHour))['is_now'] ? 1 : 0.45 }}; transition:height 0.3s;"></div>
                                @if($h % 6 === 0)
                                    <small class="text-muted mt-1 fs-62" >{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}h</small>
                                @endif
                            </div>
                        @endfor
                    </div>
                @else
                    <p class="text-muted text-center py-3 mb-0">Chưa có dữ liệu phiên trong 24h qua.</p>
                @endif
            </div>
        </div>

        {{-- Lượt truy cập 7 ngày --}}
        <div class="card border-0 it-activity-card">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">Lượt hoạt động 7 ngày</h6>
                <span class="badge bg-soft-info text-info it-panel-badge">{{ number_format($totalActivities7d) }} tổng</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 it-activity-table">
                    <thead class="text-nowrap">
                        <tr>
                            <th>Ngày</th>
                            <th>Biểu đồ</th>
                            <th class="text-end">Lượt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($last7Days as $day => $cnt)
                            <tr>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($day)->locale('vi')->isoFormat('dddd, DD/MM') }}</td>
                                <td class="w-55pct">
                                    <div class="it-disk-progress">
                                        <div class="progress-bar bg-primary" style="width:{{ $this->activityBarPct((int) $cnt, $this->maxActivityCount($last7Days)) }}%"></div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($cnt) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif

    {{-- ═══ TAB: BACKUP DB ═══ --}}
    @if($activeTab === 'backup')
        <div class="card border-0 it-panel-card">
            <div class="card-header py-3 d-flex align-items-center justify-content-between bg-gradient-white border-bottom">
                <div>
                    <h6 class="mb-0 fw-bold">Sao lưu cơ sở dữ liệu</h6>
                    <small class="text-muted">File backup được lưu tại <code>storage/app/backups/</code></small>
                </div>
                <button wire:click="backupDatabase"
                        wire:loading.attr="disabled"
                        wire:confirm="Tạo backup database ngay bây giờ?"
                        class="btn btn-sm btn-primary fw-semibold d-flex align-items-center gap-2">
                    <span wire:loading wire:target="backupDatabase" class="spinner-border spinner-border-sm"></span>
                    <svg wire:loading.remove wire:target="backupDatabase" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Tạo backup
                </button>
            </div>
            <div class="card-body p-0">
                @if(count($backupList) === 0)
                    <div class="text-center text-muted py-5">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3 d-block mx-auto opacity-50">
                            <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                        </svg>
                        <div class="fw-semibold">Chưa có file backup nào</div>
                        <small>Nhấn "Tạo backup" để bắt đầu.</small>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 it-activity-table">
                            <thead>
                                <tr>
                                    <th>Tên file</th>
                                    <th>Kích thước</th>
                                    <th>Ngày tạo</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($backupList as $backup)
                                    <tr>
                                        <td class="fw-semibold">{{ $backup['name'] }}</td>
                                        <td class="text-muted">
                                            @if($backup['size'] >= 1048576)
                                                {{ number_format($backup['size'] / 1048576, 2) }} MB
                                            @else
                                                {{ number_format($backup['size'] / 1024, 1) }} KB
                                            @endif
                                        </td>
                                        <td class="text-muted">
                                            {{ \Carbon\Carbon::createFromTimestamp($backup['mtime'])->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button wire:click="downloadBackup('{{ $backup['name'] }}')"
                                                        wire:loading.attr="disabled"
                                                        class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="7 10 12 15 17 10"></polyline>
                                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                                    </svg>
                                                    Tải
                                                </button>
                                                <button wire:click="deleteBackup('{{ $backup['name'] }}')"
                                                        wire:loading.attr="disabled"
                                                        wire:confirm="Xóa file backup này?"
                                                        class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6l-1 14H6L5 6"></path>
                                                        <path d="M10 11v6M14 11v6M9 6V4h6v2"></path>
                                                    </svg>
                                                    Xóa
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
