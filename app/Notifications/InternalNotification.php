<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class InternalNotification extends Notification
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly int $senderId,
        public readonly string $senderName,
        public readonly string $batchId,
        public readonly string $recipientsLabel,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return [
            'contract_type'    => 'internal',
            'contract_label'   => $this->title,
            'message'          => $this->body,
            'sender_id'        => $this->senderId,
            'sender_name'      => $this->senderName,
            'batch_id'         => $this->batchId,
            'recipients_label' => $this->recipientsLabel,
            'color'            => 'info',
            'icon'             => 'bi-megaphone-fill',
            'url'              => '',
        ];
    }
}
