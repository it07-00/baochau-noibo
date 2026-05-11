<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkScheduleNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $eventTitle,
        public string $userName,
        public string $action = 'created', // created, updated, deleted
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actionLabels = [
            'created' => 'tạo mới',
            'updated' => 'cập nhật',
            'deleted' => 'xóa',
        ];

        $label = $actionLabels[$this->action] ?? $this->action;

        return [
            'icon'           => 'bi-calendar-event-fill',
            'color'          => 'info',
            'contract_type'  => 'work_schedule',
            'contract_label' => 'Lịch công tác',
            'message'        => "{$this->userName} đã {$label} lịch: \"{$this->eventTitle}\"",
            'url'            => route('app.work-schedules.index'),
        ];
    }
}
