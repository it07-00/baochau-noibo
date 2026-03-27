<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $contractType,
        public int    $contractId,
        public string $contractLabel,
        public string $assignerName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $typeLabels = [
            'waste'          => 'chất thải',
            'consulting'     => 'tư vấn',
            'project'        => 'dự án',
            'commercial'     => 'thương mại',
            'sustainability' => 'phát triển bền vững',
            'energy'         => 'năng lượng',
        ];

        $label = $typeLabels[$this->contractType] ?? $this->contractType;

        return [
            'icon'           => 'bi-person-check-fill',
            'color'          => 'success',
            'contract_type'  => $this->contractType,
            'contract_id'    => $this->contractId,
            'contract_label' => $this->contractLabel,
            'message'        => "{$this->assignerName} giao cho bạn HĐ {$label}: {$this->contractLabel}",
            'url'            => route("app.contracts.{$this->contractType}.index"),
        ];
    }
}
