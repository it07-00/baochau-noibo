<?php

namespace App\Enums;

enum DailyReportSupportStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ xử lý',
            self::IN_PROGRESS => 'Đang xử lý',
            self::RESOLVED => 'Đã xử lý',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'danger',
            self::IN_PROGRESS => 'warning',
            self::RESOLVED => 'success',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
