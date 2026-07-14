<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case HEN_BAO_GIA = 'Hẹn báo giá sau';
    case DANG_THEO_DOI = 'Đang theo dõi';
    case ROT_BAO_GIA = 'Rớt báo giá';
    case KY_HOP_DONG = 'Ký hợp đồng';
    case BAO_GIA_TIEM_NANG = 'BG tiềm năng';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::DANG_THEO_DOI => 'success',
            self::KY_HOP_DONG => 'primary',
            self::ROT_BAO_GIA => 'danger',
            self::HEN_BAO_GIA => 'warning',
            self::BAO_GIA_TIEM_NANG => 'secondary',
        };
    }

    /** Mảng ['value' => 'label'] dùng cho Blade dropdown */
    public static function map(): array
    {
        return array_column(
            array_map(fn ($c) => [$c->value, $c->label()], self::cases()),
            1, 0
        );
    }

    /** Mảng values — dùng cho validation rule: Rule::in(QuotationStatus::values()) */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
