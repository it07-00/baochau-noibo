<?php

namespace App\Enums;

enum DailyReportStatus: string
{
    case HOAN_THANH_DUNG_KH = 'Hoàn thành đúng kế hoạch';
    case HOAN_THANH_MOT_PHAN = 'Hoàn thành một phần';
    case GAP_VAN_DE          = 'Gặp vấn đề, cần hỗ trợ';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::HOAN_THANH_DUNG_KH  => 'success',
            self::HOAN_THANH_MOT_PHAN => 'warning',
            self::GAP_VAN_DE          => 'danger',
        };
    }

    /** Mảng values — dùng cho validation: Rule::in(DailyReportStatus::values()) */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
