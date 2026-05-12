<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DailyReportSubmittedNotification extends Notification
{
    use Queueable;

    protected $reporterName;
    protected $reportDate;
    protected $lateDays;

    public function __construct($reporterName, $reportDate, int $lateDays = 0)
    {
        $this->reporterName = $reporterName;
        $this->reportDate = $reportDate;
        $this->lateDays = $lateDays;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'           => 'bi-file-earmark-text-fill',
            'color'          => 'primary',
            'contract_type'  => 'daily_report',
            'contract_label' => 'Báo cáo ngày',
            'message'        => "{$this->reporterName} đã gửi báo cáo ngày " . date('d/m/Y', strtotime($this->reportDate))
                . ($this->lateDays > 0 ? " (nộp trễ {$this->lateDays} ngày)" : ''),
            'url'            => route('app.daily-reports.index', ['dateFilter' => $this->reportDate, 'viewType' => 'day']),
        ];
    }
}
