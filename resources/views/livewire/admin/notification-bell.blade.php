<div class="notification-bell" x-data wire:poll.15s x-on:hidden.bs.dropdown="$wire.markViewedAsRead()">
    <a class="header-nav-link" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Thông báo">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 16V11C18 7.68629 15.3137 5 12 5C8.68629 5 6 7.68629 6 11V16L4 18V19H20V18L18 16Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"></path>
            <path d="M10.5 19C10.5 19.8284 11.1716 20.5 12 20.5C12.8284 20.5 13.5 19.8284 13.5 19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path>
        </svg>
        @if($totalBadge > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mt-1 ms-n1" >{{ $totalBadge > 99 ? '99+' : $totalBadge }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-end py-0 shadow-lg border-0 notification-panel">
        <div class="dropdown-header notification-panel-header d-flex align-items-center justify-content-between border-bottom py-3">
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
        <div class="dropdown-body notification-panel-body py-0">

            {{-- DailyReport issues --}}
            @if($issueCount > 0)
                <div class="notification-section-toggle px-3 py-2 border-bottom bg-light-subtle d-flex align-items-center justify-content-between">
                    <span class="notification-section-title fw-semibold text-uppercase text-danger">Báo cáo ngày - cần hỗ trợ</span>
                    <span class="badge bg-danger rounded-pill">{{ $issueCount }}</span>
                </div>
                @foreach($issueReports as $ir)
                    <a class="dropdown-item notification-item py-3 border-bottom d-flex align-items-start gap-2" href="{{ route('app.daily-reports.index') }}?date={{ date('Y-m-d') }}">
                        <div class="notification-icon bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation-fill "></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="notification-item-top d-flex justify-content-between align-items-start gap-2 mb-1">
                                <span class="notification-title fw-bold text-dark">{{ $ir->user->name }}</span>
                                <span class="notification-time text-muted flex-shrink-0">{{ $ir->updated_at->diffForHumans() }}</span>
                            </div>
                            <div class="notification-message text-muted">{{ $ir->issues ?: 'Cần hỗ trợ gấp: '.$ir->status }}</div>
                        </div>
                    </a>
                @endforeach
            @endif

            @foreach($notificationSections as $section)
                <div class="border-bottom"
                     x-data="notifSection({{ $this->sectionUnreadCount($section) > 0 ? 'true' : 'false' }}, '{{ $loop->index }}')"
                     x-init="init()">
                    <button type="button" class="notification-section-toggle w-100 px-3 py-2 bg-light-subtle border-0 d-flex align-items-center justify-content-between gap-2"
                        @click="toggle()">
                        <span class="notification-section-title fw-semibold text-uppercase text-start">{{ $section['label'] }}</span>
                        <div class="notification-section-meta d-flex align-items-center gap-2">
                            <span class="badge {{ $this->sectionUnreadCount($section) > 0 ? 'bg-danger' : 'bg-secondary' }} rounded-pill">{{ $section['items']->count() }}</span>
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </div>
                    </button>

                    <div x-show="open" x-cloak>
                        @foreach($section['items'] as $notif)
                            <div class="dropdown-item notification-item py-3 border-top d-flex align-items-start gap-2 {{ $notif->read_at ? '' : 'notification-item-unread' }} cursor-pointer"
                                 @if(($this->notificationData($notif)['contract_type'] ?? '') === 'internal')
                                     wire:click.stop="openInternalModal('{{ $notif->id }}')"
                                 @else
                                     wire:click="openNotification('{{ $notif->id }}')"
                                 @endif>
                                <div class="notification-icon bg-{{ $this->notificationData($notif)['color'] ?? 'primary' }}-subtle text-{{ $this->notificationData($notif)['color'] ?? 'primary' }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="bi {{ $this->notificationData($notif)['icon'] ?? 'bi-bell-fill' }} "></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="notification-item-top d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <span class="notification-title fw-bold text-dark">{{ $this->notificationData($notif)['contract_label'] ?? '' }}</span>
                                        <span class="notification-time text-muted flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if(($this->notificationData($notif)['contract_type'] ?? '') === 'work_schedule' && !empty($this->notificationData($notif)['time_label']) && $this->notificationData($notif)['time_label'] !== 'Cả ngày')
                                        <div class="text-muted small mb-1"><i class="fa-solid fa-clock me-1"></i>{{ $this->notificationData($notif)['time_label'] }}</div>
                                    @endif
                                    <div class="notification-message text-muted">{{ $this->notificationData($notif)['message'] ?? '' }}</div>
                                </div>
                                @if(!$notif->read_at)
                                    <span class="notification-unread-dot bg-primary rounded-circle flex-shrink-0 mt-2"></span>
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

    {{-- Internal Notification Detail Modal — teleported to body to avoid header stacking context --}}
    <template x-teleport="body">
        <div class="modal fade" id="internalNotifModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg overflow-hidden">
                    <div class="modal-header bg-info py-3">
                        <h5 class="modal-title fw-bold text-white">
                            <i class="fa-solid fa-bullhorn-fill me-2"></i>
                            <span id="internalNotifTitle"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3 text-muted small">
                            <i class="fa-solid fa-user-circle"></i>
                            <span id="internalNotifSender"></span>
                            <span class="ms-auto">
                                <i class="fa-solid fa-clock me-1"></i>
                                <span id="internalNotifTime"></span>
                            </span>
                        </div>
                        <hr class="my-2">
                        <div id="internalNotifBody" style="white-space: pre-wrap;"></div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
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

            window.addEventListener('openInternalNotifModal', function (event) {
                const d = event.detail || {};
                document.getElementById('internalNotifTitle').textContent  = d.title      || '';
                document.getElementById('internalNotifSender').textContent = d.senderName || '';
                document.getElementById('internalNotifTime').textContent   = d.createdAt  || '';
                document.getElementById('internalNotifBody').textContent   = d.body       || '';

                const el = document.getElementById('internalNotifModal');
                if (!el) return;
                const existing = bootstrap.Modal.getInstance(el);
                if (existing) existing.dispose();
                new bootstrap.Modal(el).show();
            });
        })();

        /**
         * notifSection — Alpine component for collapsible notification sections.
         *
         * Persists open/closed state in sessionStorage so Livewire re-renders
         * (e.g., wire:poll.15s) do not forcibly re-open sections the user has
         * manually closed.
         *
         * @param {boolean} defaultOpen  — true when section has unread items (server-side)
         * @param {string}  key          — unique key (loop index) to identify this section
         */
        function notifSection(defaultOpen, key) {
            return {
                open: false,
                _storageKey: 'notif_section_' + key,

                init() {
                    const stored = sessionStorage.getItem(this._storageKey);
                    if (stored === null) {
                        // First render: use server default (open if unread)
                        this.open = defaultOpen;
                    } else {
                        // Restore what the user last chose
                        this.open = stored === '1';
                    }
                },

                toggle() {
                    this.open = !this.open;
                    sessionStorage.setItem(this._storageKey, this.open ? '1' : '0');
                },
            };
        }
    </script>
@endonce

