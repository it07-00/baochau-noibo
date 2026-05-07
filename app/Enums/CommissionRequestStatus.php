<?php

namespace App\Enums;

enum CommissionRequestStatus: string
{
    case CHO_CHI  = 'Chờ chi';
    case DA_CHI   = 'Đã chi';
    case TU_CHOI  = 'Từ chối';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::CHO_CHI  => 'warning',
            self::DA_CHI   => 'success',
            self::TU_CHOI  => 'danger',
        };
    }

    /** Mảng ['value' => 'label'] dùng cho Blade dropdown */
    public static function map(): array
    {
        return array_column(
            array_map(fn($c) => [$c->value, $c->label()], self::cases()),
            1, 0
        );
    }

    /** Mảng values — dùng cho validation: Rule::in(CommissionRequestStatus::values()) */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
