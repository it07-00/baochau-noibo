<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractWorkflowUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $contractType,
        public int    $contractId,
        public string $contractLabel,
        public string $stepName,
        public string $stepLabel,
        public string $userName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'           => 'bi-diagram-3-fill',
            'color'          => 'info',
            'contract_type'  => $this->contractType,
            'contract_id'    => $this->contractId,
            'contract_label' => $this->contractLabel,
            'message'        => "{$this->userName} hoàn thành bước \"{$this->stepLabel}\" — HĐ {$this->contractLabel}",
            'url'            => route("app.contracts.{$this->contractType}.index"),
        ];
    }
}
