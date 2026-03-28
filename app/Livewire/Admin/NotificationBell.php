<?php

namespace App\Livewire\Admin;

use App\Models\DailyReport;
use Livewire\Component;

class NotificationBell extends Component
{
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

    public function render()
    {
        $user = auth()->user();

        // Database notifications (hợp đồng)
        $dbNotifications = $user->notifications()->latest()->take(20)->get();
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

        return view('livewire.admin.notification-bell', [
            'dbNotifications' => $dbNotifications,
            'unreadCount'     => $unreadCount,
            'issueReports'    => $issueReports,
            'issueCount'      => $issueCount,
            'totalBadge'      => $unreadCount + $issueCount,
        ]);
    }
}
