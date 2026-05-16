<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\DailyReport;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $limit = 20;
    public ?string $lastRealtimeNotificationId = null;
    public bool $realtimeInitialized = false;

    private const MAX_FETCH = 500;

    private function contractSectionKeys(): array
    {
        return [
            'waste',
            'consulting',
            'project',
            'commercial',
            'sustainability',
            'energy',
        ];
    }

    private function allowedSectionKeysForUser($user): array
    {
        return array_merge(['daily_report', 'work_schedule'], $this->contractSectionKeys());
    }

    private function sectionLabelsForUser($user): array
    {
        $allLabels = [
            'daily_report'   => 'Báo cáo ngày',
            'work_schedule'  => 'Lịch công tác',
            'waste'          => 'HĐ Chất thải và tiếng ồn',
            'consulting'     => 'HĐ Pháp lý và hồ sơ MT',
            'project'        => 'HĐ Kỹ thuật và ứng phó SC',
            'commercial'     => 'HĐ NC và chuyển đổi CN',
            'sustainability' => 'HĐ TV và báo cáo PTBV',
            'energy'         => 'HĐ Phát thải và năng lượng',
        ];

        $allowedKeys = $this->allowedSectionKeysForUser($user);

        $labels = [];
        foreach ($allowedKeys as $key) {
            if (isset($allLabels[$key])) {
                $labels[$key] = $allLabels[$key];
            }
        }

        return $labels;
    }

    private function resolveSectionKey(array $data): string
    {
        $contractType = (string) ($data['contract_type'] ?? '');

        $allKeys = array_merge(['daily_report', 'work_schedule'], $this->contractSectionKeys());

        if (in_array($contractType, $allKeys, true)) {
            return $contractType;
        }

        // Fallback for old notifications without contract_type
        $url = (string) ($data['url'] ?? '');
        if (str_contains($url, '/daily-reports')) {
            return 'daily_report';
        }
        if (str_contains($url, '/lich-cong-tac')) {
            return 'work_schedule';
        }

        return 'general';
    }

    private function visibleNotificationsForUser($user, ?int $limit = null): Collection
    {
        $allowedSections = $this->allowedSectionKeysForUser($user);

        $notifications = $user->notifications()
            ->latest()
            ->take(self::MAX_FETCH)
            ->get()
            ->filter(function ($notification) use ($allowedSections) {
                $sectionKey = $this->resolveSectionKey((array) ($notification->data ?? []));
                return in_array($sectionKey, $allowedSections, true);
            })
            ->values();

        return $limit !== null
            ? $notifications->take($limit)->values()
            : $notifications;
    }

    private function dispatchBrowserNotificationIfNeeded(Collection $notifications): void
    {
        $latest = $notifications->first();
        $latestId = $latest?->id;

        if (!$this->realtimeInitialized) {
            $this->realtimeInitialized = true;
            $this->lastRealtimeNotificationId = $latestId;
            return;
        }

        if (!$latestId || $latestId === $this->lastRealtimeNotificationId) {
            return;
        }

        $this->lastRealtimeNotificationId = $latestId;

        $data = (array) ($latest->data ?? []);
        $body = (string) ($data['message'] ?? 'Bạn có thông báo mới');
        $timeLabel = trim((string) ($data['time_label'] ?? ''));

        if (($data['contract_type'] ?? '') === 'work_schedule' && $timeLabel !== '' && $timeLabel !== 'Cả ngày') {
            $body .= ' | Giờ: ' . $timeLabel;
        }

        $this->dispatch(
            'browser-notification',
            title: (string) ($data['contract_label'] ?? 'Thông báo mới'),
            body: $body,
            url: (string) ($data['url'] ?? ''),
        );
    }

    public function markAsRead(string $id): void
    {
        $notification = auth()->user()->notifications()->find($id);
        $notification?->markAsRead();
    }

    public function openNotification(string $id): mixed
    {
        $notification = auth()->user()->notifications()->find($id);
        $url = $notification?->data['url'] ?? '/';
        $notification?->markAsRead();
        return $this->redirect($url);
    }

    public function markAllRead(): void
    {
        $user = auth()->user();

        $visibleIds = $this->visibleNotificationsForUser($user)
            ->pluck('id');

        if ($visibleIds->isEmpty()) {
            $this->dispatch('swal:toast', [
                'type' => 'info',
                'message' => 'Không có thông báo để đánh dấu đã đọc.',
            ]);
            return;
        }

        $updatedCount = $user->notifications()
            ->whereIn('id', $visibleIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->dispatch('swal:toast', [
            'type' => $updatedCount > 0 ? 'success' : 'info',
            'message' => $updatedCount > 0
                ? 'Đã đánh dấu tất cả thông báo là đã đọc.'
                : 'Không có thông báo chưa đọc.',
        ]);
    }

    public function loadMore(): void
    {
        $this->limit = min($this->limit + 20, 200);
    }

    public function markViewedAsRead(): void
    {
        $user = auth()->user();

        $visibleIds = $this->visibleNotificationsForUser($user, $this->limit)
            ->pluck('id');

        if ($visibleIds->isEmpty()) {
            return;
        }

        $user->notifications()
            ->whereIn('id', $visibleIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        $user = auth()->user();

        $allVisibleNotifications = $this->visibleNotificationsForUser($user);
        $this->dispatchBrowserNotificationIfNeeded($allVisibleNotifications);
        $dbNotifications = $allVisibleNotifications->take($this->limit)->values();
        $visibleUnreadCount = $allVisibleNotifications->whereNull('read_at')->count();

        // DailyReport issues (giữ nguyên logic cũ)
        $issueReports = [];
        $issueCount   = 0;
        if ($user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value])) {
            $issueReports = DailyReport::with('user')
                ->whereDate('date', date('Y-m-d'))
                ->where(function ($q) {
                    $q->where('status', 'Gặp vấn đề, cần hỗ trợ')
                      ->orWhereRaw("TRIM(COALESCE(issues, '')) <> ''");
                })
                ->latest()
                ->get();
            $issueCount = $issueReports->count();
        }

            $sectionLabels = $this->sectionLabelsForUser($user);

        $notificationSections = collect($sectionLabels)
            ->map(fn($label, $key) => [
                'key'   => $key,
                'label' => $label,
                'items' => collect(),
            ]);

        foreach ($dbNotifications as $notification) {
                $sectionKey = $this->resolveSectionKey((array) ($notification->data ?? []));

                if (!$notificationSections->has($sectionKey)) {
                    continue;
                }

            $section = $notificationSections->get($sectionKey);
            $section['items']->push($notification);
            $notificationSections->put($sectionKey, $section);
        }

        $notificationSections = $notificationSections->values();

        return view('livewire.admin.notification-bell', [
            'dbNotifications' => $dbNotifications,
            'notificationSections' => $notificationSections,
                'unreadCount'     => $visibleUnreadCount,
            'issueReports'    => $issueReports,
            'issueCount'      => $issueCount,
                'totalBadge'      => $visibleUnreadCount + $issueCount,
                'hasMoreNotifications' => $allVisibleNotifications->count() > $dbNotifications->count(),
        ]);
    }
}
