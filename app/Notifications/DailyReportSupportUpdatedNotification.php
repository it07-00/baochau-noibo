<?php

namespace App\Notifications;

use App\Enums\DailyReportSupportStatus;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DailyReportSupportUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly DailyReport $report,
        private readonly User $handler,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = DailyReportSupportStatus::tryFrom((string) $this->report->support_status);

        return [
            'icon' => $status === DailyReportSupportStatus::RESOLVED ? 'bi-check-circle-fill' : 'bi-life-preserver',
            'color' => $status?->color() ?? 'primary',
            'contract_type' => 'daily_report',
            'contract_label' => 'Hỗ trợ báo cáo ngày',
            'message' => $this->handler->name.' đã cập nhật yêu cầu hỗ trợ ngày '
                .$this->report->date->format('d/m/Y').' sang “'.($status?->label() ?? 'Đã cập nhật').'”.',
            'url' => route('app.daily-reports.index'),
        ];
    }
}
