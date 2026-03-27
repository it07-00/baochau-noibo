<div>
    <a class="header-nav-link" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Thông báo">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 16V11C18 7.68629 15.3137 5 12 5C8.68629 5 6 7.68629 6 11V16L4 18V19H20V18L18 16Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"></path>
            <path d="M10.5 19C10.5 19.8284 11.1716 20.5 12 20.5C12.8284 20.5 13.5 19.8284 13.5 19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path>
        </svg>
        @if($totalBadge > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="margin-top: 5px; margin-left: -5px;">{{ $totalBadge > 99 ? '99+' : $totalBadge }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-end py-0 shadow-lg border-0" style="width: 380px;">
        <div class="dropdown-header d-flex align-items-center justify-content-between border-bottom py-3">
            <h6 class="mb-0 fw-bold">Thông báo</h6>
            <div class="d-flex align-items-center gap-2">
                @if($totalBadge > 0)
                    <span class="badge bg-danger rounded-pill">{{ $totalBadge }}</span>
                @endif
                @if($unreadCount > 0)
                    <button wire:click.stop="markAllRead" class="btn btn-sm btn-link text-decoration-none p-0 small">Đọc tất cả</button>
                @endif
            </div>
        </div>
        <div class="dropdown-body py-0 overflow-auto" style="max-height: 420px;">

            {{-- DailyReport issues --}}
            @foreach($issueReports as $ir)
                <a class="dropdown-item py-3 border-bottom d-flex align-items-start gap-2" href="{{ route('app.daily-reports.index') }}?date={{ date('Y-m-d') }}">
                    <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                        <i class="bi bi-exclamation-triangle-fill small"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small text-dark">{{ $ir->user->name }}</span>
                            <span class="text-muted flex-shrink-0" style="font-size: 0.7rem;">{{ $ir->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-muted small text-truncate" style="font-size: 0.75rem;">{{ $ir->issues ?: 'Cần hỗ trợ gấp: '.$ir->status }}</div>
                    </div>
                </a>
            @endforeach

            {{-- Contract notifications --}}
            @forelse($dbNotifications as $notif)
                @php $data = $notif->data; @endphp
                <div class="dropdown-item py-3 border-bottom d-flex align-items-start gap-2 {{ $notif->read_at ? '' : 'bg-primary bg-opacity-10' }}"
                     style="cursor: pointer; white-space: normal;"
                     wire:click="openNotification('{{ $notif->id }}')">
                    <div class="bg-{{ $data['color'] ?? 'primary' }}-subtle text-{{ $data['color'] ?? 'primary' }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                        <i class="bi {{ $data['icon'] ?? 'bi-bell-fill' }} small"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small text-dark text-truncate">{{ $data['contract_label'] ?? '' }}</span>
                            <span class="text-muted flex-shrink-0 ms-2" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.75rem; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $data['message'] ?? '' }}</div>
                    </div>
                    @if(!$notif->read_at)
                        <span class="bg-primary rounded-circle flex-shrink-0 mt-2" style="width: 8px; height: 8px;"></span>
                    @endif
                </div>
            @empty
                @if($issueCount == 0)
                <div class="py-5 text-center text-muted">
                    <i class="bi bi-bell-slash d-block fs-3 mb-2 opacity-25"></i>
                    <span class="small">Chưa có thông báo nào.</span>
                </div>
                @endif
            @endforelse
        </div>
    </div>
</div>
