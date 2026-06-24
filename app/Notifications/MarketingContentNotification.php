<?php

namespace App\Notifications;

use App\Models\MarketingContent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarketingContentNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected MarketingContent $content,
        protected string $type, // 'submitted', 'approved', 'rejected'
        protected ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $creatorName = $this->content->user?->name ?? 'Nhân viên';
        $title = $this->content->title;

        if ($this->type === 'submitted') {
            $message = "{$creatorName} đã gửi duyệt bài viết: \"{$title}\"";
            $color = 'warning';
        } elseif ($this->type === 'approved') {
            $message = "Bài viết \"{$title}\" của bạn đã được phê duyệt.";
            $color = 'success';
        } else {
            $message = "Bài viết \"{$title}\" của bạn bị từ chối." . ($this->reason ? " Lý do: {$this->reason}" : '');
            $color = 'danger';
        }

        return [
            'icon'           => 'bi-megaphone-fill',
            'color'          => $color,
            'contract_type'  => 'marketing',
            'contract_label' => 'Kế hoạch content',
            'message'        => $message,
            'url'            => route('app.marketing.content.index'),
        ];
    }
}
