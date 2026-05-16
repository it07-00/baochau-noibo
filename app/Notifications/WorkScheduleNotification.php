<?php

namespace App\Notifications;

use App\Enums\Role as RoleEnum;
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
        public ?string $eventTimeLabel = null,
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
        $timeLabel = $this->normalizeTimeLabel($this->eventTimeLabel);
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
            'time_label'     => $timeLabel,
            'url'            => $this->resolveNotificationUrl($notifiable, $urlParameters),
        ];
    }

    private function resolveNotificationUrl(object $notifiable, array $urlParameters): string
    {
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole(RoleEnum::GIAM_DOC->value)) {
            return route('app.dashboard');
        }

        return route('app.work-schedules.index', $urlParameters);
    }

    private function normalizeTimeLabel(?string $timeLabel): ?string
    {
        $value = trim((string) $timeLabel);

        return $value !== '' ? $value : null;
    }
}
