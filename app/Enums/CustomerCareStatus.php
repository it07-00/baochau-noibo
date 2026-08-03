<?php

namespace App\Enums;

enum CustomerCareStatus: string
{
    case NOT_CONTACTED = 'not_contacted';
    case CONTACTED     = 'contacted';
    case IN_PROGRESS   = 'in_progress';
    case SIGNED        = 'signed';
    case REJECTED      = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::NOT_CONTACTED => 'Chưa liên hệ',
            self::CONTACTED     => 'Đã liên hệ',
            self::IN_PROGRESS   => 'Đang đàm phán',
            self::SIGNED        => 'Đã ký hợp đồng',
            self::REJECTED      => 'Từ chối dịch vụ',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::NOT_CONTACTED => 'bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle',
            self::CONTACTED     => 'bg-info bg-opacity-10 text-info border border-info-subtle',
            self::IN_PROGRESS   => 'bg-warning bg-opacity-10 text-warning border border-warning-subtle',
            self::SIGNED        => 'bg-success bg-opacity-10 text-success border border-success-subtle',
            self::REJECTED      => 'bg-danger bg-opacity-10 text-danger border border-danger-subtle',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
