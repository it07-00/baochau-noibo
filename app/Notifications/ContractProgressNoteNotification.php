<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractProgressNoteNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $contractType,
        public int    $contractId,
        public string $contractLabel,
        public string $notePreview,
        public string $userName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'           => 'bi-journal-text',
            'color'          => 'warning',
            'contract_type'  => $this->contractType,
            'contract_id'    => $this->contractId,
            'contract_label' => $this->contractLabel,
            'message'        => "{$this->userName} ghi chú HĐ {$this->contractLabel}: \"{$this->notePreview}\"",
            'url'            => route("app.contracts.{$this->contractType}.index"),
        ];
    }
}
