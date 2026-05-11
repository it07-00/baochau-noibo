<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DailyReportReminderNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'           => 'bi-clock-fill',
            'color'          => 'warning',
            'contract_type'  => 'daily_report',
            'contract_label' => 'Báo cáo ngày',
            'message'        => 'Nhắc nhở: Bạn chưa gửi báo cáo ngày hôm nay. Vui lòng gửi trước khi kết thúc ngày làm việc.',
            'url'            => route('app.daily-reports.index'),
        ];
    }
}
