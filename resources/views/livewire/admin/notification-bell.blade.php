<div x-data wire:poll.15s x-on:hidden.bs.dropdown="$wire.markViewedAsRead()">
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
                <button
                    type="button"
                    wire:click.stop="markAllRead"
                    class="btn btn-sm btn-link text-decoration-none p-0 "
                >
                    Đọc tất cả
                    ({{ $unreadCount }})
                </button>
            </div>
        </div>
        <div class="dropdown-body py-0 overflow-auto" style="max-height: 420px;">

            {{-- DailyReport issues --}}
            @if($issueCount > 0)
                <div class="px-3 py-2 border-bottom bg-light-subtle d-flex align-items-center justify-content-between">
                    <span class=" fw-semibold text-uppercase text-danger">Báo cáo ngày - cần hỗ trợ</span>
                    <span class="badge bg-danger rounded-pill">{{ $issueCount }}</span>
                </div>
                @foreach($issueReports as $ir)
                    <a class="dropdown-item py-3 border-bottom d-flex align-items-start gap-2" href="{{ route('app.daily-reports.index') }}?date={{ date('Y-m-d') }}">
                        <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                            <i class="bi bi-exclamation-triangle-fill "></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold  text-dark">{{ $ir->user->name }}</span>
                                <span class="text-muted flex-shrink-0" style="font-size: 0.7rem;">{{ $ir->updated_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-muted  text-truncate" style="font-size: 0.75rem;">{{ $ir->issues ?: 'Cần hỗ trợ gấp: '.$ir->status }}</div>
                        </div>
                    </a>
                @endforeach
            @endif

            @foreach($notificationSections as $section)
                @php $sectionUnread = $section['items']->whereNull('read_at')->count(); @endphp
                <div class="border-bottom" x-data="{ open: {{ $sectionUnread > 0 ? 'true' : 'false' }} }">
                    <button type="button" class="w-100 px-3 py-2 bg-light-subtle border-0 d-flex align-items-center justify-content-between"
                        @click="open = !open">
                        <span class=" fw-semibold text-uppercase text-start">{{ $section['label'] }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $sectionUnread > 0 ? 'bg-danger' : 'bg-secondary' }} rounded-pill">{{ $section['items']->count() }}</span>
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </div>
                    </button>

                    <div x-show="open" x-cloak>
                        @foreach($section['items'] as $notif)
                            @php $data = $notif->data; @endphp
                            <div class="dropdown-item py-3 border-top d-flex align-items-start gap-2 {{ $notif->read_at ? '' : 'bg-primary bg-opacity-10' }}"
                                 style="cursor: pointer; white-space: normal;"
                                 wire:click="openNotification('{{ $notif->id }}')">
                                <div class="bg-{{ $data['color'] ?? 'primary' }}-subtle text-{{ $data['color'] ?? 'primary' }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                    <i class="bi {{ $data['icon'] ?? 'bi-bell-fill' }} "></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold  text-dark text-truncate">{{ $data['contract_label'] ?? '' }}</span>
                                        <span class="text-muted flex-shrink-0 ms-2" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-muted " style="font-size: 0.75rem; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $data['message'] ?? '' }}</div>
                                </div>
                                @if(!$notif->read_at)
                                    <span class="bg-primary rounded-circle flex-shrink-0 mt-2" style="width: 8px; height: 8px;"></span>
                                @endif
                            </div>
                        @endforeach

                        @if($section['items']->isEmpty())
                            <div class="px-3 py-2 text-muted  fst-italic border-top">
                                Chưa có thông báo trong mục này.
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if($hasMoreNotifications)
                <div class="py-2 text-center border-top bg-light-subtle">
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click.stop="loadMore">
                        Xem thêm thông báo
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@once
    <script>
        (function () {
            if (window.__bcBrowserNotificationHooked) {
                return;
            }
            window.__bcBrowserNotificationHooked = true;

            window.addEventListener('browser-notification', function (event) {
                if (typeof Notification === 'undefined') {
                    return;
                }

                const detail = event.detail || {};
                const title = String(detail.title || 'Thông báo mới');
                const bodyRaw = String(detail.body || '');
                const body = bodyRaw.length > 180 ? bodyRaw.slice(0, 180) + '...' : bodyRaw;
                const url = String(detail.url || '');

                const showBrowserNotification = function () {
                    const notification = new Notification(title, { body });

                    notification.onclick = function () {
                        window.focus();
                        if (url) {
                            window.location.href = url;
                        }
                        notification.close();
                    };
                };

                if (Notification.permission === 'granted') {
                    showBrowserNotification();
                    return;
                }

                if (Notification.permission === 'default') {
                    Notification.requestPermission().then(function (permission) {
                        if (permission === 'granted') {
                            showBrowserNotification();
                        }
                    });
                }
            });
        })();
    </script>
@endonce
