<?php

namespace App\Enums;

enum Role: string
{
    case IT             = 'it';
    case GIAM_DOC       = 'giam-doc';
    case QUAN_LY        = 'quan-ly';
    case TP_KINH_DOANH  = 'tp-kinh-doanh';
    case KINH_DOANH     = 'kinh-doanh';
    case TU_VAN         = 'tu-van';
    case KY_THUAT       = 'ky-thuat';
    case MARKETING      = 'marketing';
    case KE_TOAN        = 'ke-toan';

    public function label(): string
    {
        return match($this) {
            self::IT            => 'IT / Hệ thống',
            self::GIAM_DOC      => 'Giám đốc',
            self::QUAN_LY       => 'Quản lý',
            self::TP_KINH_DOANH => 'TP. Kinh doanh',
            self::KINH_DOANH    => 'Kinh doanh',
            self::TU_VAN        => 'Tư vấn / CSKH',
            self::KY_THUAT      => 'Kỹ thuật',
            self::MARKETING     => 'Marketing',
            self::KE_TOAN       => 'Kế toán',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::IT            => 'purple',
            self::GIAM_DOC      => 'red',
            self::QUAN_LY       => 'orange',
            self::TP_KINH_DOANH => 'blue',
            self::KINH_DOANH    => 'sky',
            self::TU_VAN        => 'green',
            self::KY_THUAT      => 'yellow',
            self::MARKETING     => 'pink',
            self::KE_TOAN       => 'indigo',
        };
    }

    public static function toMiddleware(self ...$roles): string
    {
        return 'role:' . implode(',', array_map(fn($r) => $r->value, $roles));
    }
}
