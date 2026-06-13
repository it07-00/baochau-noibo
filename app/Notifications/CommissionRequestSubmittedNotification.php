<?php

namespace App\Notifications;

use App\Enums\CommissionRequestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommissionRequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $requesterName,
        public string $contractLabel,
        public string $amount,
        public int $requestId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $amountText = number_format((float) $this->amount, 0, ',', '.');

        return [
            'icon' => 'bi-cash-stack',
            'color' => 'warning',
            'contract_type' => 'commission',
            'contract_label' => 'Yêu cầu hoa hồng mới',
            'message' => "{$this->requesterName} vừa gửi yêu cầu chi hoa hồng {$amountText} VND cho hợp đồng {$this->contractLabel}.",
            'url' => route('app.commissions.index'),
            'request_id' => $this->requestId,
            'status' => CommissionRequestStatus::DU_CHI->value,
        ];
    }
}
