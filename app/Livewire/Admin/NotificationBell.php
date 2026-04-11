<?php

namespace App\Livewire\Admin;

use App\Models\DailyReport;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $limit = 20;

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
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function loadMore(): void
    {
        $this->limit = min($this->limit + 20, 200);
    }

    public function markViewedAsRead(): void
    {
        $user = auth()->user();

        $visibleIds = $user->notifications()
            ->latest()
            ->take($this->limit)
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

        // Database notifications (hợp đồng)
        $dbNotifications = $user->notifications()->latest()->take($this->limit)->get();
        $totalDbNotifications = $user->notifications()->count();
        $unreadCount     = $user->unreadNotifications()->count();

        // DailyReport issues (giữ nguyên logic cũ)
        $issueReports = [];
        $issueCount   = 0;
        if ($user->hasAnyRole(['giam-doc', 'quan-ly', 'it'])) {
            $issueReports = DailyReport::with('user')
                ->whereDate('date', date('Y-m-d'))
                ->where(function ($q) {
                    $q->where('status', 'Gặp vấn đề, cần hỗ trợ')
                      ->orWhereNotNull('issues');
                })
                ->latest()
                ->get();
            $issueCount = $issueReports->count();
        }

        $sectionLabels = [
            'daily_report'   => 'Báo cáo ngày',
            'waste'          => 'Hợp đồng chất thải',
            'consulting'     => 'Hợp đồng tư vấn',
            'project'        => 'Hợp đồng dự án',
            'commercial'     => 'Hợp đồng thương mại',
            'sustainability' => 'Hợp đồng PTBV',
            'energy'         => 'Hợp đồng năng lượng',
            'other'          => 'Thông báo khác',
        ];

        $notificationSections = collect($sectionLabels)
            ->map(fn($label, $key) => [
                'key'   => $key,
                'label' => $label,
                'items' => collect(),
            ]);

        foreach ($dbNotifications as $notification) {
            $data = $notification->data ?? [];
            $contractType = $data['contract_type'] ?? null;
            $url = (string) ($data['url'] ?? '');
            $contractLabel = (string) ($data['contract_label'] ?? '');

            $sectionKey = 'other';
            if (
                $contractLabel === 'Báo cáo ngày'
                || str_contains($url, '/daily-reports')
            ) {
                $sectionKey = 'daily_report';
            } elseif (array_key_exists((string) $contractType, $sectionLabels)) {
                $sectionKey = (string) $contractType;
            }

            $section = $notificationSections->get($sectionKey);
            $section['items']->push($notification);
            $notificationSections->put($sectionKey, $section);
        }

        $notificationSections = $notificationSections->values();

        return view('livewire.admin.notification-bell', [
            'dbNotifications' => $dbNotifications,
            'notificationSections' => $notificationSections,
            'unreadCount'     => $unreadCount,
            'issueReports'    => $issueReports,
            'issueCount'      => $issueCount,
            'totalBadge'      => $unreadCount + $issueCount,
            'hasMoreNotifications' => $totalDbNotifications > $dbNotifications->count(),
        ]);
    }
}
