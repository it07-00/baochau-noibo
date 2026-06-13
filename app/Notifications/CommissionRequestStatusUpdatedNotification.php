<?php

namespace App\Notifications;

use App\Enums\CommissionRequestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommissionRequestStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $status,
        public string $processedByName,
        public string $contractLabel,
        public string $amount,
        public int $requestId,
        public ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $amountText = number_format((float) $this->amount, 0, ',', '.');
        $status = trim($this->status);

        if ($status === CommissionRequestStatus::DA_DUYET->value) {
            $message = "Yêu cầu chi hoa hồng {$amountText} VND cho hợp đồng {$this->contractLabel} đã được {$this->processedByName} duyệt, đang chờ chi.";
            $icon = 'bi-patch-check-fill';
            $color = 'warning';
        } elseif ($status === CommissionRequestStatus::DA_CHI->value) {
            $message = "Yêu cầu chi hoa hồng {$amountText} VND cho hợp đồng {$this->contractLabel} đã được {$this->processedByName} xác nhận đã chi.";
            $icon = 'bi-patch-check-fill';
            $color = 'success';
        } elseif ($status === CommissionRequestStatus::TU_CHOI->value) {
            $reasonText = $this->reason ? ' Lý do: '.trim($this->reason).'.' : '';
            $message = "Yêu cầu chi hoa hồng {$amountText} VND cho hợp đồng {$this->contractLabel} đã bị {$this->processedByName} từ chối.".$reasonText;
            $icon = 'bi-x-octagon-fill';
            $color = 'danger';
        } else {
            $message = "Yêu cầu chi hoa hồng {$amountText} VND cho hợp đồng {$this->contractLabel} đã được cập nhật sang trạng thái {$status}.";
            $icon = 'bi-info-circle-fill';
            $color = 'info';
        }

        return [
            'icon' => $icon,
            'color' => $color,
            'contract_type' => 'commission',
            'contract_label' => 'Cập nhật yêu cầu hoa hồng',
            'message' => $message,
            'url' => route('app.commissions.index'),
            'request_id' => $this->requestId,
            'status' => $status,
        ];
    }
}
