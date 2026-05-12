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
        public string $action = 'created', // created, updated, deleted, added
        public ?string $eventDate = null,
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
            'added' => 'thêm bạn vào',
        ];

        $label = $actionLabels[$this->action] ?? $this->action;
        $urlParameters = [];

        if ($this->eventDate) {
            $timestamp = strtotime($this->eventDate);

            if ($timestamp !== false) {
                $urlParameters = [
                    'monthFilter' => (int) date('m', $timestamp),
                    'yearFilter' => (int) date('Y', $timestamp),
                ];
            }
        }

        return [
            'icon'           => 'bi-calendar-event-fill',
            'color'          => 'info',
            'contract_type'  => 'work_schedule',
            'contract_label' => 'Lịch công tác',
            'message'        => $this->action === 'added'
                ? "{$this->userName} đã thêm bạn vào lịch công tác: \"{$this->eventTitle}\""
                : "{$this->userName} đã {$label} lịch: \"{$this->eventTitle}\"",
            'url'            => route('app.work-schedules.index', $urlParameters),
        ];
    }
}
